<?php

/*
	Editor module class for WYSIWYG editor plugin
*/


class wysiwyg_editor
{
	private $urltoroot;

	public function load_module($directory, $urltoroot)
	{
		$this->urltoroot = $urltoroot;
	}

	public function option_default($option)
	{
		if ($option == 'wysiwyg_editor_upload_max_size') {
			require_once INCLUDE_DIR.'app/upload.php';

			return min(qa_get_max_upload_size(), 1048576);
		}
	}

	public function admin_form(&$qa_content)
	{
		require_once INCLUDE_DIR.'app/upload.php';

		$saved = false;

		if (qa_clicked('wysiwyg_editor_save_button')) {
			qa_opt('wysiwyg_editor_upload_images', (int)qa_post_text('wysiwyg_editor_upload_images_field'));
			qa_opt('wysiwyg_editor_upload_all', (int)qa_post_text('wysiwyg_editor_upload_all_field'));
			qa_opt('wysiwyg_editor_upload_max_size', min(qa_get_max_upload_size(), 1048576*(float)qa_post_text('wysiwyg_editor_upload_max_size_field')));
			$saved = true;
		}

		qa_set_display_rules($qa_content, array(
			'wysiwyg_editor_upload_all_display' => 'wysiwyg_editor_upload_images_field',
			'wysiwyg_editor_upload_max_size_display' => 'wysiwyg_editor_upload_images_field',
		));

		// handle AJAX requests to 'wysiwyg-editor-ajax'
		$js = array(
			'function wysiwyg_editor_ajax(totalEdited) {',
			'	$.ajax({',
			'		url: ' . qa_js(qa_path('wysiwyg-editor-ajax')) . ',',
			'		success: function(response) {',
			'			var postsEdited = parseInt(response, 10);',
			'			var $btn = $("#wysiwyg_editor_ajax");',
			'			if (isNaN(postsEdited)) {',
			'				$btn.text("ERROR");',
			'			}',
			'			else if (postsEdited < 5) {',
			'				$btn.text("All posts converted.");',
			'			}',
			'			else {',
			'				totalEdited += postsEdited;',
			'				$btn.text("Updating posts... " + totalEdited)',
			'				window.setTimeout(function() {',
			'					wysiwyg_editor_ajax(totalEdited);',
			'				}, 1000);',
			'			}',
			'		}',
			'	});',
			'}',

			'$("#wysiwyg_editor_ajax").click(function() {',
			'	wysiwyg_editor_ajax(0);',
			'	return false;',
			'});',
		);
		$ajaxHtml = 'Update broken images from old CKeditor Smiley plugin: ' .
			'<button id="wysiwyg_editor_ajax">click here</button> ' .
			'<script>' . implode("\n", $js) . '</script>';

		return array(
			'ok' => $saved ? 'WYSIWYG editor settings saved' : null,

			'fields' => array(
				array(
					'label' => 'Allow images to be uploaded',
					'type' => 'checkbox',
					'value' => (int)qa_opt('wysiwyg_editor_upload_images'),
					'tags' => 'name="wysiwyg_editor_upload_images_field" id="wysiwyg_editor_upload_images_field"',
				),

				array(
					'id' => 'wysiwyg_editor_upload_all_display',
					'label' => 'Allow other content to be uploaded, e.g. Flash, PDF',
					'type' => 'checkbox',
					'value' => (int)qa_opt('wysiwyg_editor_upload_all'),
					'tags' => 'name="wysiwyg_editor_upload_all_field"',
				),

				array(
					'id' => 'wysiwyg_editor_upload_max_size_display',
					'label' => 'Maximum size of uploads:',
					'suffix' => 'MB (max '.qa_html(number_format($this->bytes_to_mega(qa_get_max_upload_size()), 1)).')',
					'type' => 'number',
					'value' => qa_html(number_format($this->bytes_to_mega(qa_opt('wysiwyg_editor_upload_max_size')), 1)),
					'tags' => 'name="wysiwyg_editor_upload_max_size_field"',
				),

				array(
					'type' => 'custom',
					'html' => $ajaxHtml,
				),
			),

			'buttons' => array(
				array(
					'label' => 'Save Changes',
					'tags' => 'name="wysiwyg_editor_save_button"',
				),
			),
		);
	}

	public function calc_quality($content, $format)
	{
		if ($format == 'html')
			return 1.0;
		elseif ($format == '')
			return 0.8;
		else
			return 0;
	}

	public function get_field(&$qa_content, $content, $format, $fieldname, $rows)
	{
		$scriptsrc = '/ckeditor/ckeditor.js';

		$alreadyadded = false;

		if (isset($qa_content['script_src'])) {
			foreach ($qa_content['script_src'] as $testscriptsrc) {
				if ($testscriptsrc == $scriptsrc)
					$alreadyadded = true;
			}
		}

		if (!$alreadyadded) {
			$uploadimages = qa_opt('wysiwyg_editor_upload_images');
			$uploadall = $uploadimages && qa_opt('wysiwyg_editor_upload_all');
			$imageUploadUrl = qa_js( qa_path('wysiwyg-editor-upload', array('qa_only_image' => true)) );
			$fileUploadUrl = qa_js( qa_path('wysiwyg-editor-upload') );

			$lang = trim(qa_js(qa_opt('site_language')), "\"\'");
			if (!$lang)
				$lang = 'en';	

			$qa_content['script_src'][] = $scriptsrc;

			// CKeditor options
			$qa_content['script_lines'][] = [				
				"var editor_config = {",				
				($uploadimages ? "	filebrowserImageUploadUrl: $imageUploadUrl," : ""),
				($uploadall ? "	filebrowserUploadUrl: $fileUploadUrl," : ""),
				"	filebrowserUploadMethod: 'form',", // Use form upload instead of XHR				
				"	defaultLanguage: '$lang',",
				"	language: '$lang',",
				"	height: 350,",
				"};",
			];
		}

		if ($format == 'html') {
			$html = $content;
			$text = $this->html_to_text($content);
		}
		else {
			$text = $content;
			$html = qa_html($content, true);
		}

		return array(
			'tags' => 'name="'.$fieldname.'"',
			'value' => qa_html($text),
			'rows' => $rows,
			'html_prefix' => '<input name="'.$fieldname.'_ckeditor_ok" id="'.$fieldname.'_ckeditor_ok" type="hidden" value="0"><input name="'.$fieldname.'_ckeditor_data" id="'.$fieldname.'_ckeditor_data" type="hidden" value="'.qa_html($html).'">',
		);
	}

	public function load_script($fieldname)
	{
		return
			"if (qa_ckeditor_".$fieldname." = CKEDITOR.replace(".qa_js($fieldname).", editor_config)) { " .
				"qa_ckeditor_".$fieldname.".setData(document.getElementById(".qa_js($fieldname.'_ckeditor_data').").value); " .
				"document.getElementById(".qa_js($fieldname.'_ckeditor_ok').").value = 1; " .
			"}";
	}

	public function focus_script($fieldname)
	{
		return "if (qa_ckeditor_".$fieldname.") qa_ckeditor_".$fieldname.".focus();";
	}

	public function update_script($fieldname)
	{
		return "if (qa_ckeditor_".$fieldname.") qa_ckeditor_".$fieldname.".updateElement();";
	}

    // WYSIWYG Editor -> Sanitize HTML

	public function read_post($fieldname)
	{
		if (qa_post_text($fieldname.'_ckeditor_ok')) {

			// CKEditor was loaded successfully
			$html = qa_post_text($fieldname);

            // Remove potentially harmful tags like iframe and <js> scripts
            // See insights : qa-viewer-basic -> get_text

            // http://www.bioinformatics.org/phplabware/internal_utilities/htmLawed/htmLawed_README.htm

            require_once INCLUDE_DIR . 'vendor/htmLawed.php';

            // Sanitize all scripts out from HTML
            $config = [
                'safe' => 1,
                'elements' => '* -script -object -form +embed +iframe'
            ];

            // Нужно очистить стили у <pre> и <code>, добавленные CK Editor

            $spec = 'pre=-*;code=-*;p=-*;br=-*;strong=-*';

            $safe = htmLawed($html, $config, $spec);

            return [
                'format' => 'html',
                'content' => $safe,
            ];

		} else {
			// CKEditor was not loaded so treat it as plain text
			return [
				'format' => '',
				'content' => qa_post_text($fieldname),
			];
		}
	}


	private function html_to_text($html)
	{
		$viewer = qa_load_module('viewer', '');
		return $viewer->get_text($html, 'html', array());
	}

	private function bytes_to_mega($bytes)
	{
		return $bytes / 1048576;
	}
}
