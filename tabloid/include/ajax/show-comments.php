<?php
/*
	Description: Server-side response to Ajax request to view full comment list
*/

require_once INCLUDE_DIR . 'db/selects.php';
require_once INCLUDE_DIR . 'app/users.php';
require_once INCLUDE_DIR . 'app/cookies.php';
require_once INCLUDE_DIR . 'app/format.php';
require_once INCLUDE_DIR . 'pages/question-view.php';
require_once INCLUDE_DIR . 'util/sort.php';


// Load relevant information about this question and check it exists

$questionid = qa_post_text('c_questionid');
$parentid = qa_post_text('c_parentid');
$userid = qa_get_logged_in_userid();

list($question, $parent, $children, $duplicateposts) = qa_db_select_with_pending(
	qa_db_full_post_selectspec($userid, $questionid),
	qa_db_full_post_selectspec($userid, $parentid),
	qa_db_full_child_posts_selectspec($userid, $parentid),
	qa_db_post_duplicates_selectspec($questionid)
);

if (isset($parent)) {
	$parent = $parent + qa_page_q_post_rules($parent, null, null, $children + $duplicateposts);
	// in theory we should retrieve the parent's parent and siblings for the above, but they're not going to be relevant

	foreach ($children as $key => $child) {
		$children[$key] = $child + qa_page_q_post_rules($child, $parent, $children, null);
	}

	$commentsfollows = $questionid == $parentid
		? qa_page_q_load_c_follows($question, $children, array(), $duplicateposts)
		: qa_page_q_load_c_follows($question, array(), $children);

	$usershtml = qa_userids_handles_html($commentsfollows, true);

	qa_sort_by($commentsfollows, 'created');

	$c_list = qa_page_q_comment_follow_list($question, $parent, $commentsfollows, true, $usershtml, false, null);

	$themeclass = qa_load_theme_class(qa_get_site_theme(), 'ajax-comments', null, null);
	$themeclass->initialize();

	echo "AJAX_RESPONSE\n1\n";


	// Send back the HTML

	$themeclass->c_list_items($c_list['cs']);

	return;
}


echo "AJAX_RESPONSE\n0\n";
