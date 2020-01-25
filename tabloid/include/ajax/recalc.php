<?php
/*
	Description: Server-side response to Ajax admin recalculation requests
*/

require_once INCLUDE_DIR . 'app/users.php';
require_once INCLUDE_DIR . 'app/recalc.php';


if (qa_get_logged_in_level() >= USER_LEVEL_ADMIN) {
	if (!qa_check_form_security_code('admin/recalc', qa_post_text('code'))) {
		$state = '';
		$message = qa_lang('misc/form_security_reload');

	} else {
		$state = qa_post_text('state');
		$stoptime = time() + 3;

		while (qa_recalc_perform_step($state) && time() < $stoptime) {
			// wait
		}

		$message = qa_recalc_get_message($state);
	}

} else {
	$state = '';
	$message = qa_lang('admin/no_privileges');
}


echo "AJAX_RESPONSE\n1\n" . $state . "\n" . qa_html($message);
