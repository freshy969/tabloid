<?php
/*
	Description: Server-side response to Ajax create comment requests
*/

require_once INCLUDE_DIR . 'app/users.php';
require_once INCLUDE_DIR . 'app/limits.php';
require_once INCLUDE_DIR . 'db/selects.php';


// Load relevant information about this question and the comment parent

$questionid = qa_post_text('c_questionid');
$parentid = qa_post_text('c_parentid');
$userid = qa_get_logged_in_userid();

list($question, $parent, $children) = qa_db_select_with_pending(
	qa_db_full_post_selectspec($userid, $questionid),
	qa_db_full_post_selectspec($userid, $parentid),
	qa_db_full_child_posts_selectspec($userid, $parentid)
);


// Check if the question and parent exist, and whether the user has permission to do this

if (@$question['basetype'] == 'Q' && (@$parent['basetype'] == 'Q' || @$parent['basetype'] == 'A') &&
	!qa_user_post_permit_error('permit_post_c', $parent,LIMIT_COMMENTS)
) {
	require_once INCLUDE_DIR . 'app/captcha.php';
	require_once INCLUDE_DIR . 'app/format.php';
	require_once INCLUDE_DIR . 'app/post-create.php';
	require_once INCLUDE_DIR . 'app/cookies.php';
	require_once INCLUDE_DIR . 'pages/question-view.php';
	require_once INCLUDE_DIR . 'pages/question-submit.php';
	require_once INCLUDE_DIR . 'util/sort.php';


	// Try to create the new comment

	$usecaptcha = qa_user_use_captcha(qa_user_level_for_post($question));
	$commentid = qa_page_q_add_c_submit($question, $parent, $children, $usecaptcha, $in, $errors);


	// If successful, page content will be updated via Ajax

	if (isset($commentid)) {
		$children = qa_db_select_with_pending(qa_db_full_child_posts_selectspec($userid, $parentid));

		$parent = $parent + qa_page_q_post_rules($parent, ($questionid == $parentid) ? null : $question, null, $children);
		// in theory we should retrieve the parent's siblings for the above, but they're not going to be relevant

		foreach ($children as $key => $child) {
			$children[$key] = $child + qa_page_q_post_rules($child, $parent, $children, null);
		}

		$usershtml = qa_userids_handles_html($children, true);

		qa_sort_by($children, 'created');

		$c_list = qa_page_q_comment_follow_list($question, $parent, $children, true, $usershtml, false, null);

		$themeclass = qa_load_theme_class(qa_get_site_theme(), 'ajax-comments', null, null);
		$themeclass->initialize();

		echo "AJAX_RESPONSE\n1\n";


		// send back the ID of the new comment
		echo qa_anchor('C', $commentid) . "\n";


		// send back the HTML
		$themeclass->c_list_items($c_list['cs']);

		return;
	}
}

echo "AJAX_RESPONSE\n0\n"; // fall back to non-Ajax submission if there were any problems
