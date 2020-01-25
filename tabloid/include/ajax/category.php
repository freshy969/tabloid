<?php

/*
	Server-side response to Ajax category information requests
*/

require_once INCLUDE_DIR . 'db/selects.php';


$categoryid = qa_post_text('categoryid');
if (!strlen($categoryid))
	$categoryid = null;

list($fullcategory, $categories) = qa_db_select_with_pending(
	qa_db_full_category_selectspec($categoryid, true),
	qa_db_category_sub_selectspec($categoryid)
);

echo "AJAX_RESPONSE\n1\n";

echo qa_html(strtr(@$fullcategory['content'], "\r\n", '  ')); // category description

foreach ($categories as $category) {
	// subcategory information
	echo "\n" . $category['categoryid'] . '/' . $category['title'];
}
