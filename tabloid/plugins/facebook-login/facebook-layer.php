<?php

/*
	Theme layer class for mouseover layer plugin
*/

class html_theme_layer extends html_theme_base
{
	public function head_css()
	{
		html_theme_base::head_css();

		if (strlen(qa_opt('facebook_app_id')) && strlen(qa_opt('facebook_app_secret'))) {
			$this->output(
				'<style>',
				'.fb-login-button.fb_iframe_widget.fb_hide_iframes span {display:none;}',
				'</style>'
			);
		}
	}
}
