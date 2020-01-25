<?php

/*
	Controller for user profile page
*/


// Determine the identify of the user

$handle = qa_request_part(1);

if (!strlen($handle)) {
	$handle = qa_get_logged_in_handle();
	qa_redirect(!empty($handle) ? 'user/' . $handle : 'users');
}


// Get the HTML to display for the handle, and if we're using external users, determine the userid

if (FINAL_EXTERNAL_USERS) {
	$userid = qa_handle_to_userid($handle);
	if (!isset($userid))
		return include INCLUDE_DIR . 'page-not-found.php';

	$usershtml = qa_get_users_html(array($userid), false, qa_path_to_root(), true);
	$userhtml = @$usershtml[$userid];

} else
	$userhtml = qa_html($handle);


// Display the appropriate page based on the request

switch (qa_request_part(2)) {
	case 'wall':
		qa_set_template('user-wall');
		$qa_content = include INCLUDE_DIR . 'pages/user-wall.php';
		break;

	case 'activity':
		qa_set_template('user-activity');
		$qa_content = include INCLUDE_DIR . 'pages/user-activity.php';
		break;

	case 'questions':
		qa_set_template('user-questions');
		$qa_content = include INCLUDE_DIR . 'pages/user-questions.php';
		break;

	case 'answers':
		qa_set_template('user-answers');
		$qa_content = include INCLUDE_DIR . 'pages/user-answers.php';
		break;

	case null:
		$qa_content = include INCLUDE_DIR . 'pages/user-profile.php';
		break;

	default:
		$qa_content = include INCLUDE_DIR . 'page-not-found.php';
		break;
}

return $qa_content;
