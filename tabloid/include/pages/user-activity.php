<?php
/*
	Controller for user page showing recent activity
*/


require_once INCLUDE_DIR . 'db/selects.php';
require_once INCLUDE_DIR . 'app/format.php';


// $handle, $userhtml are already set by /qa-include/page/user.php - also $userid if using external user integration


// Find the recent activity for this user

$loginuserid = qa_get_logged_in_userid();
$identifier = FINAL_EXTERNAL_USERS ? $userid : $handle;

list($useraccount, $questions, $answerqs, $commentqs, $editqs) = qa_db_select_with_pending(
	FINAL_EXTERNAL_USERS ? null : qa_db_user_account_selectspec($handle, false),
	qa_db_user_recent_qs_selectspec($loginuserid, $identifier, qa_opt_if_loaded('page_size_activity')),
	qa_db_user_recent_a_qs_selectspec($loginuserid, $identifier),
	qa_db_user_recent_c_qs_selectspec($loginuserid, $identifier),
	qa_db_user_recent_edit_qs_selectspec($loginuserid, $identifier)
);

if (!FINAL_EXTERNAL_USERS && !is_array($useraccount)) // check the user exists
	return include INCLUDE_DIR . 'page-not-found.php';


// Get information on user references

$questions = qa_any_sort_and_dedupe(array_merge($questions, $answerqs, $commentqs, $editqs));
$questions = array_slice($questions, 0, qa_opt('page_size_activity'));
$usershtml = qa_userids_handles_html(qa_any_get_userids_handles($questions), false);


// Prepare content for theme

$qa_content = qa_content_prepare(true);

if (count($questions))
	$qa_content['title'] = qa_lang_html_sub('profile/recent_activity_by_x', $userhtml);
else
	$qa_content['title'] = qa_lang_html_sub('profile/no_posts_by_x', $userhtml);


// Recent activity by this user

$qa_content['q_list']['form'] = array(
	'tags' => 'method="post" action="' . qa_self_html() . '"',

	'hidden' => array(
		'code' => qa_get_form_security_code('vote'),
	),
);

$qa_content['q_list']['qs'] = array();

$htmldefaults = qa_post_html_defaults('Q');
$htmldefaults['whoview'] = false;
$htmldefaults['voteview'] = false;
$htmldefaults['avatarsize'] = 0;

foreach ($questions as $question) {
	$qa_content['q_list']['qs'][] = qa_any_to_q_html_fields($question, $loginuserid, qa_cookie_get(),
		$usershtml, null, array('voteview' => false) + qa_post_html_options($question, $htmldefaults));
}


// Sub menu for navigation in user pages

$ismyuser = isset($loginuserid) && $loginuserid == (FINAL_EXTERNAL_USERS ? $userid : $useraccount['userid']);
$qa_content['navigation']['sub'] = qa_user_sub_navigation($handle, 'activity', $ismyuser);


return $qa_content;
