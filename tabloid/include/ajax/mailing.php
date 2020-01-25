<?php
/*
	Description: Server-side response to Ajax mailing loop requests
*/

require_once INCLUDE_DIR . 'app/users.php';
require_once INCLUDE_DIR . 'app/mailing.php';


$continue = false;

if (qa_get_logged_in_level() >= USER_LEVEL_ADMIN) {
	$starttime = time();

	qa_mailing_perform_step();

	if ($starttime == time())
		sleep(1); // make sure at least one second has passed

	$message = qa_mailing_progress_message();

	if (isset($message))
		$continue = true;
	else
		$message = qa_lang('admin/mailing_complete');

} else
	$message = qa_lang('admin/no_privileges');


echo "AJAX_RESPONSE\n" . (int)$continue . "\n" . qa_html($message);
