<?php

/*
	Generic module class for mouseover layer plugin to provide admin form and default option
*/

class mouseover_admin_form
{
	public function option_default($option)
	{
		if ($option === 'mouseover_content_max_len')
			return 480;
	}


	public function admin_form(&$qa_content)
	{
		$saved = qa_clicked('mouseover_save_button');

		if ($saved) {
			qa_opt('mouseover_content_on', (int) qa_post_text('mouseover_content_on_field'));
			qa_opt('mouseover_content_max_len', (int) qa_post_text('mouseover_content_max_len_field'));
		}

		qa_set_display_rules($qa_content, array(
			'mouseover_content_max_len_display' => 'mouseover_content_on_field',
		));

		return array(
			'ok' => $saved ? 'Mouseover settings saved' : null,

			'fields' => array(
				array(
					'label' => 'Show content preview on mouseover in question lists',
					'type' => 'checkbox',
					'value' => qa_opt('mouseover_content_on'),
					'tags' => 'name="mouseover_content_on_field" id="mouseover_content_on_field"',
				),

				array(
					'id' => 'mouseover_content_max_len_display',
					'label' => 'Maximum length of preview:',
					'suffix' => 'characters',
					'type' => 'number',
					'value' => (int) qa_opt('mouseover_content_max_len'),
					'tags' => 'name="mouseover_content_max_len_field"',
				),
			),

			'buttons' => array(
				array(
					'label' => 'Save Changes',
					'tags' => 'name="mouseover_save_button"',
				),
			),
		);
	}
}
