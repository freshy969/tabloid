<?php

/*
	Database-level access to table containing admin options
*/


/**
 * Set option $name to $value in the database
 * @param $name
 * @param $value
 */
function qa_db_set_option($name, $value)
{
	qa_db_query_sub(
		'INSERT INTO ^options (title, content) VALUES ($, $) ' .
		'ON DUPLICATE KEY UPDATE content = VALUES(content)',
		$name, $value
	);
}
