<?php
/*
	Description: Server-side response to Ajax voting requests
*/

require_once INCLUDE_DIR . 'app/users.php';
require_once INCLUDE_DIR . 'app/cookies.php';
require_once INCLUDE_DIR . 'app/votes.php';
require_once INCLUDE_DIR . 'app/format.php';
require_once INCLUDE_DIR . 'app/options.php';
require_once INCLUDE_DIR . 'db/selects.php';


$postid = qa_post_text('postid');
$vote = qa_post_text('vote');
$code = qa_post_text('code');

$userid = qa_get_logged_in_userid();
$cookieid = qa_cookie_get();

if (!qa_check_form_security_code('vote', $code)) {
	$voteerror = qa_lang_html('misc/form_security_reload');
} else {
	$post = qa_db_select_with_pending(qa_db_full_post_selectspec($userid, $postid));
	$voteerror = qa_vote_error_html($post, $vote, $userid, qa_request());
}

if ($voteerror === false) {
	qa_vote_set($post, $userid, qa_get_logged_in_handle(), $cookieid, $vote);

	$post = qa_db_select_with_pending(qa_db_full_post_selectspec($userid, $postid));

	$fields = qa_post_html_fields($post, $userid, $cookieid, array(), null, array(
		'voteview' => qa_get_vote_view($post, true), // behave as if on question page since the vote succeeded
	));

	$themeclass = qa_load_theme_class(qa_get_site_theme(), 'voting', null, null);
	$themeclass->initialize();

	echo "AJAX_RESPONSE\n1\n";
	$themeclass->voting_inner_html($fields);

	return;

}

echo "AJAX_RESPONSE\n0\n" . $voteerror;
