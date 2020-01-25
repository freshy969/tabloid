<?php
/*
	Description: Server-side response to Ajax single clicks on wall posts
*/

require_once INCLUDE_DIR . 'app/messages.php';
require_once INCLUDE_DIR . 'app/users.php';
require_once INCLUDE_DIR . 'app/cookies.php';
require_once INCLUDE_DIR . 'db/selects.php';


$tohandle = qa_post_text('handle');
$start = (int)qa_post_text('start');

$usermessages = qa_db_select_with_pending(qa_db_recent_messages_selectspec(null, null, $tohandle, false, null, $start));
$usermessages = qa_wall_posts_add_rules($usermessages, $start);

foreach ($usermessages as $message) {
	if (qa_clicked('m' . $message['messageid'] . '_dodelete') && $message['deleteable']) {
		if (qa_check_form_security_code('wall-' . $tohandle, qa_post_text('code'))) {
			qa_wall_delete_post(qa_get_logged_in_userid(), qa_get_logged_in_handle(), qa_cookie_get(), $message);
			echo "AJAX_RESPONSE\n1\n";
			return;
		}
	}
}

echo "AJAX_RESPONSE\n0\n";
