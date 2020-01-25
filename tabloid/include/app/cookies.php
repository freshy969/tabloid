<?php

/*
	User cookie management (application level) for tracking anonymous posts
*/

/**
 * Return the user identification cookie sent by the browser for this page request, or null if none
 */
function qa_cookie_get()
{
	if (qa_to_override(__FUNCTION__)) { $args=func_get_args(); return qa_call_override(__FUNCTION__, $args); }

	return isset($_COOKIE['qa_id']) ? qa_gpc_to_string($_COOKIE['qa_id']) : null;
}


/**
 * Return user identification cookie sent by browser if valid, or create a new one if not.
 * Either way, extend for another year (this is used when an anonymous post is created)
 */
function qa_cookie_get_create()
{
	if (qa_to_override(__FUNCTION__)) { $args=func_get_args(); return qa_call_override(__FUNCTION__, $args); }

	require_once INCLUDE_DIR . 'db/cookies.php';

	$cookieid = qa_cookie_get();

	if (!isset($cookieid) || !qa_db_cookie_exists($cookieid)) {
		// cookie is invalid
		$cookieid = qa_db_cookie_create(qa_remote_ip_address());
	}

	setcookie('qa_id', $cookieid, time() + 86400 * 365, '/', COOKIE_DOMAIN, (bool)ini_get('session.cookie_secure'), true);
	$_COOKIE['qa_id'] = $cookieid;

	return $cookieid;
}


/**
 * Called after a database write $action performed by a user identified by $cookieid,
 * relating to $questionid, $answerid and/or $commentid
 * @param $cookieid
 * @param $action
 */
function qa_cookie_report_action($cookieid, $action)
{
	require_once INCLUDE_DIR . 'db/cookies.php';

	qa_db_cookie_written($cookieid, qa_remote_ip_address());
}
