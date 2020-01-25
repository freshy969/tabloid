<?php

/*
	Controller for page listing hot questions
*/


require_once INCLUDE_DIR . 'db/selects.php';
require_once INCLUDE_DIR . 'app/q-list.php';


// Get list of hottest questions, allow per-category ifALLOW_UNINDEXED_QUERIES set in qa-config.php

$categoryslugs = ALLOW_UNINDEXED_QUERIES ? qa_request_parts(1) : null;
$countslugs = @count($categoryslugs);

$start = qa_get_start();
$userid = qa_get_logged_in_userid();

list($questions, $categories, $categoryid) = qa_db_select_with_pending(
	qa_db_qs_selectspec($userid, 'hotness', $start, $categoryslugs, null, false, false, qa_opt_if_loaded('page_size_hot_qs')),
	qa_db_category_nav_selectspec($categoryslugs, false, false, true),
	$countslugs ? qa_db_slugs_to_category_id_selectspec($categoryslugs) : null
);

if ($countslugs) {
	if (!isset($categoryid))
		return include INCLUDE_DIR . 'page-not-found.php';

	$categorytitlehtml = qa_html($categories[$categoryid]['title']);
	$sometitle = qa_lang_html_sub('main/hot_qs_in_x', $categorytitlehtml);
	$nonetitle = qa_lang_html_sub('main/no_questions_in_x', $categorytitlehtml);

} else {
	$sometitle = qa_lang_html('main/hot_qs_title');
	$nonetitle = qa_lang_html('main/no_questions_found');
}


// Prepare and return content for theme

return qa_q_list_page_content(
	$questions, // questions
	qa_opt('page_size_hot_qs'), // questions per page
	$start, // start offset
	$countslugs ? $categories[$categoryid]['qcount'] : qa_opt('cache_qcount'), // total count
	$sometitle, // title if some questions
	$nonetitle, // title if no questions
	ALLOW_UNINDEXED_QUERIES ? $categories : array(), // categories for navigation
	$categoryid, // selected category id
	true, // show question counts in category navigation
	ALLOW_UNINDEXED_QUERIES ? 'hot/' : null, // prefix for links in category navigation (null if no navigation)
	qa_opt('feed_for_hot') ? 'hot' : null, // prefix for RSS feed paths (null to hide)
	qa_html_suggest_ask() // suggest what to do next
);
