<?php

/*
	Server-side response to Ajax single clicks on posts in admin section
*/

require_once INCLUDE_DIR . 'app/admin.php';
require_once INCLUDE_DIR . 'app/users.php';
require_once INCLUDE_DIR . 'app/cookies.php';


$entityid = qa_post_text('entityid');
$action = qa_post_text('action');

if (!qa_check_form_security_code('admin/click', qa_post_text('code')))
	echo "AJAX_RESPONSE\n0\n" . qa_lang('misc/form_security_reload');
elseif (qa_admin_single_click($entityid, $action)) // permission check happens in here
	echo "AJAX_RESPONSE\n1\n";
else
	echo "AJAX_RESPONSE\n0\n" . qa_lang('main/general_error');
