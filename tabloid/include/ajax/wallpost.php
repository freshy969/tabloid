<?php
/*
	Description: Server-side response to Ajax wall post requests
*/

require_once INCLUDE_DIR . 'app/messages.php';
require_once INCLUDE_DIR . 'app/users.php';
require_once INCLUDE_DIR . 'app/cookies.php';
require_once INCLUDE_DIR . 'db/selects.php';


$message = qa_post_text('message');
$tohandle = qa_post_text('handle');
$morelink = qa_post_text('morelink');

$touseraccount = qa_db_select_with_pending(qa_db_user_account_selectspec($tohandle, false));
$loginuserid = qa_get_logged_in_userid();

$errorhtml = qa_wall_error_html($loginuserid, $touseraccount['userid'], $touseraccount['flags']);

if ($errorhtml || !strlen($message) || !qa_check_form_security_code('wall-' . $tohandle, qa_post_text('code'))) {
	echo "AJAX_RESPONSE\n0"; // if there's an error, process in non-Ajax way
} else {
	$messageid = qa_wall_add_post($loginuserid, qa_get_logged_in_handle(), qa_cookie_get(),
		$touseraccount['userid'], $touseraccount['handle'], $message, '');
	$touseraccount['wallposts']++; // won't have been updated

	$usermessages = qa_db_select_with_pending(qa_db_recent_messages_selectspec(null, null, $touseraccount['userid'], true, qa_opt('page_size_wall')));
	$usermessages = qa_wall_posts_add_rules($usermessages, 0);

	$themeclass = qa_load_theme_class(qa_get_site_theme(), 'wall', null, null);
	$themeclass->initialize();

	echo "AJAX_RESPONSE\n1\n";

	echo 'm' . $messageid . "\n"; // element in list to be revealed

	foreach ($usermessages as $message) {
		$themeclass->message_item(qa_wall_post_view($message));
	}

	if ($morelink && ($touseraccount['wallposts'] > count($usermessages)))
		$themeclass->message_item(qa_wall_view_more_link($tohandle, count($usermessages)));
}
