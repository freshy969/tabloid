<?php

/*
	Initialization for page requests
*/

require_once INCLUDE_DIR . 'app/page.php';


// Below are the steps that actually execute for this file - all the above are function definitions

global $qa_usage;

qa_report_process_stage('init_page');
qa_db_connect('qa_page_db_fail_handler');
qa_initialize_postdb_plugins();

qa_page_queue_pending();
qa_load_state();
qa_check_login_modules();

if (DEBUG_PERFORMANCE)
	$qa_usage->mark('setup');

qa_check_page_clicks();

$qa_content = qa_get_request_content();

if (is_array($qa_content)) {
	if (DEBUG_PERFORMANCE)
		$qa_usage->mark('view');

	qa_output_content($qa_content);

	if (DEBUG_PERFORMANCE)
		$qa_usage->mark('theme');

	if (qa_do_content_stats($qa_content) && DEBUG_PERFORMANCE)
		$qa_usage->mark('stats');

	if (DEBUG_PERFORMANCE)
		$qa_usage->output();
}

qa_db_disconnect();
