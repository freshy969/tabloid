<?php

/*
	Controller for logout page (not much to do)
*/


if (FINAL_EXTERNAL_USERS) {
	$request = qa_request();
	$topath = qa_get('to'); // lets user switch between login and register without losing destination page
	$userlinks = qa_get_login_links(qa_path_to_root(), isset($topath) ? $topath : qa_path($request, $_GET, ''));

	if (!empty($userlinks['logout'])) {
		qa_redirect_raw($userlinks['logout']);
	}
	qa_fatal_error('User logout should be handled by external code');
}

if (qa_is_logged_in()) {
	qa_set_logged_in_user(null);
}

qa_redirect(''); // back to home page
