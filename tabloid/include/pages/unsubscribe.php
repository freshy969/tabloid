<?php

/*
	Controller for unsubscribe page (unsubscribe link is sent in mass mailings)
*/


require_once INCLUDE_DIR . 'db/users.php';


// Check we're not using single-sign on integration

if (FINAL_EXTERNAL_USERS)
	qa_fatal_error('User login is handled by external code');


// Check the code and unsubscribe the user if appropriate

// check if already unsubscribed
$unsubscribed = (bool) (qa_get_logged_in_flags() & USER_FLAGS_NO_MAILINGS);
$loggedInUserId = qa_get_logged_in_userid();
$isLoggedIn = $loggedInUserId !== null;

if (qa_clicked('dounsubscribe')) {
	if (!qa_check_form_security_code('unsubscribe', qa_post_text('formcode'))) {
		$pageError = qa_lang_html('misc/form_security_again');

	} else {
		if ($isLoggedIn) {
			// logged in users can unsubscribe right away
			qa_db_user_set_flag($loggedInUserId, USER_FLAGS_NO_MAILINGS, true);
			$unsubscribed = true;

		} else {
			// logged out users require valid code (from email link)
			$incode = trim(qa_post_text('code'));
			$inhandle = qa_post_text('handle');

			if (!empty($inhandle)) {
				$userinfo = qa_db_select_with_pending(qa_db_user_account_selectspec($inhandle, false));

				if (strtolower(trim(@$userinfo['emailcode'])) == strtolower($incode)) {
					qa_db_user_set_flag($userinfo['userid'], USER_FLAGS_NO_MAILINGS, true);
					$unsubscribed = true;
				}
			}

			if (!$unsubscribed) {
				$pageError = qa_insert_login_links(qa_lang_html('users/unsubscribe_wrong_log_in'), 'unsubscribe');
			}
		}
	}
}


// Prepare content for theme

$qa_content = qa_content_prepare();

$qa_content['title'] = qa_lang_html('users/unsubscribe_title');

if ($unsubscribed) {
	$qa_content['success'] = strtr(qa_lang_html('users/unsubscribe_complete'), array(
		'^0' => qa_html(qa_opt('site_title')),
		'^1' => '<a href="' . qa_path_html('account') . '">',
		'^2' => '</a>',
	));

} elseif (!empty($pageError)) {
	$qa_content['error'] = $pageError;

} else {
	$contentForm = array(
		'tags' => 'method="post" action="' . qa_path_html('unsubscribe') . '"',

		'style' => 'wide',

		'fields' => array(),

		'buttons' => array(
			'send' => array(
				'tags' => 'name="dounsubscribe"',
				'label' => qa_lang_html('users/unsubscribe_title'),
			),
		),

		'hidden' => array(
			'formcode' => qa_get_form_security_code('unsubscribe'),
		),
	);

	if ($isLoggedIn) {
		// user is logged in: show button to confirm unsubscribe
		$contentForm['fields']['email'] = array(
			'type' => 'static',
			'label' => qa_lang_html('users/email_label'),
			'value' => qa_html(qa_get_logged_in_email()),
		);

	} else {
		// user is not logged in: show form with email address
		$incode = trim(qa_get('c'));
		$inhandle = qa_get('u');

		if (empty($incode) || empty($inhandle)) {
			$qa_content['error'] = qa_insert_login_links(qa_lang_html('users/unsubscribe_wrong_log_in'), 'account');
			$contentForm = null;
		} else {
			$contentForm['fields']['handle'] = array(
				'type' => 'static',
				'label' => qa_lang_html('users/handle_label'),
				'value' => qa_html($inhandle),
			);
			$contentForm['hidden']['code'] = qa_html($incode);
			$contentForm['hidden']['handle'] = qa_html($inhandle);
		}
	}

	if ($contentForm) {
		$qa_content['form'] = $contentForm;
	}
}

return $qa_content;
