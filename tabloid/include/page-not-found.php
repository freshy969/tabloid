<?php

/*
	Controller for page not found (error 404)
*/

require_once INCLUDE_DIR . 'app/format.php';


header('HTTP/1.0 404 Not Found');

qa_set_template('not-found');

$qa_content = qa_content_prepare();
$qa_content['error'] = qa_lang_html('main/page_not_found');
$qa_content['suggest_next'] = qa_html_suggest_qs_tags(qa_using_tags());


return $qa_content;
