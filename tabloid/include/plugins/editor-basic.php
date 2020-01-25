<?php

/*
	Basic editor module for plain text editing
*/

class editor_basic
{
	public function load_module($localdir, $htmldir)
	{
	}

	public function calc_quality($content, $format)
	{
		if ($format == '')
			return 1.0;

		if ($format == 'html')
			return 0.2;

		return 0;
	}

	public function get_field(&$qa_content, $content, $format, $fieldname, $rows /* $autofocus parameter deprecated */)
	{
		return array(
			'type' => 'textarea',
			'tags' => 'name="' . $fieldname . '" id="' . $fieldname . '"',
			'value' => qa_html($content),
			'rows' => $rows,
		);
	}

	public function focus_script($fieldname)
	{
		return "document.getElementById('" . $fieldname . "').focus();";
	}

	public function read_post($fieldname)
	{
		return array(
			'format' => '',
			'content' => qa_post_text($fieldname),
		);
	}
}
