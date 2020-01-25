<?php
/*
	Description: Server-side response to Ajax requests to close a notice
*/

require_once INCLUDE_DIR . 'app/users.php';
require_once INCLUDE_DIR . 'db/notices.php';
require_once INCLUDE_DIR . 'db/users.php';


$noticeid = qa_post_text('noticeid');

if (!qa_check_form_security_code('notice-' . $noticeid, qa_post_text('code')))
	echo "AJAX_RESPONSE\n0\n" . qa_lang('misc/form_security_reload');

else {
	if ($noticeid == 'visitor')
		setcookie('qa_noticed', 1, time() + 86400 * 3650, '/', COOKIE_DOMAIN, (bool)ini_get('session.cookie_secure'), true);

	else {
		$userid = qa_get_logged_in_userid();

		if ($noticeid == 'welcome')
			qa_db_user_set_flag($userid, USER_FLAGS_WELCOME_NOTICE, false);
		else
			qa_db_usernotice_delete($userid, $noticeid);
	}


	echo "AJAX_RESPONSE\n1";
}
