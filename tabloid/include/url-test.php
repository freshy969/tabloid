<?php

/*
	Sits in an iframe and shows a green page with word 'OK'
*/

if (qa_gpc_to_string(@$_GET['param']) == URL_TEST_STRING) {
	require_once INCLUDE_DIR . 'app/admin.php';

	echo '<html><body style="margin:0; padding:0;">';
	echo '<table width="100%" height="100%" cellspacing="0" cellpadding="0">';
	echo '<tr valign="middle"><td align="center" style="border: 1px solid; background-color:#fff; ';
	echo qa_admin_url_test_html();
	echo '/td></tr></table>';
	echo '</body></html>';
}
