<?php

/*
	Database-level access to messages table for private message history
*/

/**
 * Record a message sent from $fromuserid to $touserid with $content in $format in the database. $public sets whether
 * public (on wall) or private. Return the messageid of the row created.
 * @param $fromuserid
 * @param $touserid
 * @param $content
 * @param $format
 * @param bool $public
 * @return mixed
 */
function qa_db_message_create($fromuserid, $touserid, $content, $format, $public = false)
{
	qa_db_query_sub(
		'INSERT INTO ^messages (type, fromuserid, touserid, content, format, created) VALUES ($, #, #, $, $, NOW())',
		$public ? 'PUBLIC' : 'PRIVATE', $fromuserid, $touserid, $content, $format
	);

	return qa_db_last_insert_id();
}


/**
 * Hide the message with $messageid, in $box (inbox|outbox) from the user.
 * @param $messageid
 * @param $box
 */
function qa_db_message_user_hide($messageid, $box)
{
	$field = ($box === 'inbox' ? 'tohidden' : 'fromhidden');

	qa_db_query_sub(
		"UPDATE ^messages SET $field=1 WHERE messageid=#",
		$messageid
	);
}


/**
 * Delete the message with $messageid from the database.
 * @param $messageid
 * @param bool $public
 */
function qa_db_message_delete($messageid, $public = true)
{
	// delete PM only if both sender and receiver have hidden it
	$clause = $public ? '' : ' AND fromhidden=1 AND tohidden=1';

	qa_db_query_sub(
		'DELETE FROM ^messages WHERE messageid=#' . $clause,
		$messageid
	);
}


/**
 * Recalculate the cached count of wall posts for user $userid in the database
 * @param $userid
 */
function qa_db_user_recount_posts($userid)
{
	if (qa_should_update_counts()) {
		qa_db_query_sub(
			"UPDATE ^users AS x, (SELECT COUNT(*) AS wallposts FROM ^messages WHERE touserid=# AND type='PUBLIC') AS a SET x.wallposts=a.wallposts WHERE x.userid=#",
			$userid, $userid
		);
	}
}
