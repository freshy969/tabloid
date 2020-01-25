<?php

/*
	Response to blob requests, outputting blob from the database
*/


// Ensure no PHP errors are shown in the blob response

@ini_set('display_errors', 0);

function qa_blob_db_fail_handler()
{
	header('HTTP/1.1 500 Internal Server Error');
	qa_exit('error');
}


// Load the Q2A base file which sets up a bunch of crucial stuff

$autoconnect = false;
require 'base.php';

qa_report_process_stage('init_blob');


// Output the blob in question

require_once INCLUDE_DIR . 'app/blobs.php';

qa_db_connect('qa_blob_db_fail_handler');
qa_initialize_postdb_plugins();

$blob = qa_read_blob(qa_get('qa_blobid'));

if (isset($blob) && isset($blob['content'])) {
	// allows browsers and proxies to cache the blob (30 days)
	header('Cache-Control: max-age=2592000, public');

	$disposition = 'inline';

	switch ($blob['format']) {
		case 'jpeg':
		case 'jpg':
			header('Content-Type: image/jpeg');
			break;

		case 'gif':
			header('Content-Type: image/gif');
			break;

		case 'png':
			header('Content-Type: image/png');
			break;

		case 'pdf':
			header('Content-Type: application/pdf');
			break;

		case 'swf':
			header('Content-Type: application/x-shockwave-flash');
			break;

		default:
			header('Content-Type: application/octet-stream');
			$disposition = 'attachment';
			break;
	}

	// for compatibility with HTTP headers and all browsers
	$filename = preg_replace('/[^A-Za-z0-9 \\._-]+/', '', $blob['filename']);
	header('Content-Disposition: ' . $disposition . '; filename="' . $filename . '"');

	echo $blob['content'];

} else {
	header('HTTP/1.0 404 Not Found');
}

qa_db_disconnect();
