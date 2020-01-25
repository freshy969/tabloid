<?php

/*
	Database access functions for user cookies
*/

/**
 * Create a new random cookie for $ipaddress and insert into database, returning it
 * @param $ipaddress
 * @return null|string
 */
function qa_db_cookie_create($ipaddress)
{
	for ($attempt = 0; $attempt < 10; $attempt++) {
		$cookieid = qa_db_random_bigint();

		if (qa_db_cookie_exists($cookieid))
			continue;

		qa_db_query_sub(
			'INSERT INTO ^cookies (cookieid, created, createip) ' .
			'VALUES (#, NOW(), UNHEX($))',
			$cookieid, bin2hex(@inet_pton($ipaddress))
		);

		return $cookieid;
	}

	return null;
}


/**
 * Note in database that a write operation has been done by user identified by $cookieid and from $ipaddress
 * @param $cookieid
 * @param $ipaddress
 */
function qa_db_cookie_written($cookieid, $ipaddress)
{
	qa_db_query_sub(
		'UPDATE ^cookies SET written=NOW(), writeip=UNHEX($) WHERE cookieid=#',
		bin2hex(@inet_pton($ipaddress)), $cookieid
	);
}


/**
 * Return whether $cookieid exists in database
 * @param $cookieid
 * @return bool
 */
function qa_db_cookie_exists($cookieid)
{
	$cookie = qa_db_read_one_value(qa_db_query_sub(
		'SELECT COUNT(*) FROM ^cookies WHERE cookieid=#',
		$cookieid
	));

	return $cookie > 0;
}
