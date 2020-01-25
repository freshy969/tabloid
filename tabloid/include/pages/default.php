<?php

/*
	Controller for home page, Q&A listing page, custom pages and plugin pages
*/

require_once INCLUDE_DIR . 'db/selects.php';
require_once INCLUDE_DIR . 'app/format.php';

// Implementing pagination

$start = qa_get_start();
$userid = qa_get_logged_in_userid();

// Determine whether path begins with qa or not (question and answer listing can be accessed either way)

$requestparts = explode('/', qa_request());
$explicitqa = (strtolower($requestparts[0]) == 'qa');

if ($explicitqa) {
	$slugs = array_slice($requestparts, 1);
} elseif (strlen($requestparts[0])) {
	$slugs = $requestparts;
} else {
	$slugs = array();
}

$countslugs = count($slugs);

$sort = ($countslugs && !ALLOW_UNINDEXED_QUERIES) ? null : qa_get('sort');
$linkparams = array('sort' => $sort);

// NOW main page with pagination

$selectsort = 'created';

list($questions, $categories, $categoryid, $custompage) = qa_db_select_with_pending(
	qa_db_qs_selectspec($userid, $selectsort, $start, $slugs, null, false, false, qa_opt_if_loaded('page_size_qs')),
	qa_db_category_nav_selectspec($slugs, false, false, true),
	$countslugs ? qa_db_slugs_to_category_id_selectspec($slugs) : null,
	($countslugs == 1 && !$explicitqa) ? qa_db_page_full_selectspec($slugs[0], false) : null
);

// First, if this matches a custom page, return immediately with that page's content

if (isset($custompage) && !($custompage['flags'] & PAGE_FLAGS_EXTERNAL)) {
	qa_set_template('custom-' . $custompage['pageid']);

	$qa_content = qa_content_prepare();

	$level = qa_get_logged_in_level();

	if (!qa_permit_value_error($custompage['permit'], $userid, $level, qa_get_logged_in_flags()) || !isset($custompage['permit'])) {
		$qa_content['title'] = qa_html($custompage['heading']);
		$qa_content['custom'] = $custompage['content'];

		if ($level >= USER_LEVEL_ADMIN) {
			$qa_content['navigation']['sub'] = array(
				'admin/pages' => array(
					'label' => qa_lang('admin/edit_custom_page'),
					'url' => qa_path_html('admin/pages', array('edit' => $custompage['pageid'])),
				),
			);
		}

	} else {
		$qa_content['error'] = qa_lang_html('users/no_permission');
	}

	return $qa_content;
}


// Then, see if we should redirect because the 'qa' page is the same as the home page

if ($explicitqa && !qa_is_http_post() && !qa_has_custom_home()) {
	qa_redirect(qa_category_path_request($categories, $categoryid), $_GET);
}


// Then, if there's a slug that matches no category, check page modules provided by plugins

if (!$explicitqa && $countslugs && !isset($categoryid)) {
	$pagemodules = qa_load_modules_with('page', 'match_request');
	$request = qa_request();

	foreach ($pagemodules as $pagemodule) {
		if ($pagemodule->match_request($request)) {
			$tmpl = isset($custompage['pageid']) ? 'custom-' . $custompage['pageid'] : 'custom';
			qa_set_template($tmpl);
			return $pagemodule->process_request($request);
		}
	}
}


// Then, check whether we are showing a custom home page

if (!$explicitqa && !$countslugs && qa_opt('show_custom_home')) {
	qa_set_template('custom');
	$qa_content = qa_content_prepare();
	$qa_content['title'] = qa_html(qa_opt('custom_home_heading'));
	$qa_content['custom'] = qa_opt('custom_home_content');
	return $qa_content;
}


// If we got this far, it's a good old-fashioned Q&A listing page

require_once INCLUDE_DIR . 'app/q-list.php';

qa_set_template('qa');
$pagesize = qa_opt('page_size_home');

if ($countslugs) {
	if (!isset($categoryid)) {
		return include INCLUDE_DIR . 'page-not-found.php';
	}

	$categorytitlehtml = qa_html($categories[$categoryid]['title']);
	$sometitle = qa_lang_html_sub('main/recent_qs_as_in_x', $categorytitlehtml);
	$nonetitle = qa_lang_html_sub('main/no_questions_in_x', $categorytitlehtml);

} else {
	$sometitle = qa_lang_html('main/recent_qs_as_title');
	$nonetitle = qa_lang_html('main/no_questions_found');
}

// Update the cached count in the database of the number of questions (excluding hidden/queued)

$qa_content = qa_q_list_page_content(
	$questions, // questions
	qa_opt('page_size_qs'), // $pagesize, // questions per page
	$start, // 0, // start offset
	$countslugs ? $categories[$categoryid]['qcount'] : qa_opt('cache_qcount'), // total count // null, // total count (null to hide page links)
	$sometitle, // title if some questions
	$nonetitle, // title if no questions
	$categories, // categories for navigation
	$categoryid, // selected category id
	true, // show question counts in category navigation
	'', // $explicitqa ? 'qa/' : '', // prefix for links in category navigation
	qa_opt('feed_for_qa') ? 'qa' : null, // prefix for RSS feed paths (null to hide)
	//(count($questions) < $pagesize) // suggest what to do next
	//	? qa_html_suggest_ask($categoryid)
	//	: qa_html_suggest_qs_tags(qa_using_tags(), qa_category_path_request($categories, $categoryid)),
	//null, // page link params
	//null // category nav params
    $countslugs ? qa_html_suggest_qs_tags(qa_using_tags()) : qa_html_suggest_ask($categoryid), // suggest what to do next
	$linkparams, // extra parameters for page links
	$linkparams // category nav params
);


return $qa_content;
