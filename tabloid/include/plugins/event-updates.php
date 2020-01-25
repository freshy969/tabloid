<?php

/*
	Event module for maintaining events tables
*/

class event_updates
{
	public function process_event($event, $userid, $handle, $cookieid, $params)
	{
		if (@$params['silent']) // don't create updates about silent edits, and possibly other silent events in future
			return;

		require_once INCLUDE_DIR . 'db/events.php';
		require_once INCLUDE_DIR . 'app/events.php';

		switch ($event) {
			case 'q_post':
				if (isset($params['parent'])) // question is following an answer
					qa_create_event_for_q_user($params['parent']['parentid'], $params['postid'], UPDATE_FOLLOWS, $userid, $params['parent']['userid']);

				qa_create_event_for_q_user($params['postid'], $params['postid'], null, $userid);
				qa_create_event_for_tags($params['tags'], $params['postid'], null, $userid);
				qa_create_event_for_category($params['categoryid'], $params['postid'], null, $userid);
				break;


			case 'a_post':
				qa_create_event_for_q_user($params['parentid'], $params['postid'], null, $userid, $params['parent']['userid']);
				break;


			case 'c_post':
				$keyuserids = array();

				foreach ($params['thread'] as $comment) // previous comments in thread (but not author of parent again)
				{
					if (isset($comment['userid']))
						$keyuserids[$comment['userid']] = true;
				}

				foreach ($keyuserids as $keyuserid => $dummy) {
					if ($keyuserid != $userid)
						qa_db_event_create_not_entity($keyuserid, $params['questionid'], $params['postid'], UPDATE_FOLLOWS, $userid);
				}

				switch ($params['parent']['basetype']) {
					case 'Q':
						$updatetype = UPDATE_C_FOR_Q;
						break;

					case 'A':
						$updatetype = UPDATE_C_FOR_A;
						break;

					default:
						$updatetype = null;
						break;
				}

				// give precedence to 'your comment followed' rather than 'your Q/A commented' if both are true
				qa_create_event_for_q_user($params['questionid'], $params['postid'], $updatetype, $userid,
					@$keyuserids[$params['parent']['userid']] ? null : $params['parent']['userid']);
				break;


			case 'q_edit':
				if ($params['titlechanged'] || $params['contentchanged'])
					$updatetype = UPDATE_CONTENT;
				elseif ($params['tagschanged'])
					$updatetype = UPDATE_TAGS;
				else
					$updatetype = null;

				if (isset($updatetype)) {
					qa_create_event_for_q_user($params['postid'], $params['postid'], $updatetype, $userid, $params['oldquestion']['userid']);

					if ($params['tagschanged'])
						qa_create_event_for_tags($params['tags'], $params['postid'], UPDATE_TAGS, $userid);
				}
				break;


			case 'a_select':
				qa_create_event_for_q_user($params['parentid'], $params['postid'], UPDATE_SELECTED, $userid, $params['answer']['userid']);
				break;


			case 'q_reopen':
			case 'q_close':
				qa_create_event_for_q_user($params['postid'], $params['postid'], UPDATE_CLOSED, $userid, $params['oldquestion']['userid']);
				break;


			case 'q_hide':
				if (isset($params['oldquestion']['userid']))
					qa_db_event_create_not_entity($params['oldquestion']['userid'], $params['postid'], $params['postid'], UPDATE_VISIBLE, $userid);
				break;


			case 'q_reshow':
				qa_create_event_for_q_user($params['postid'], $params['postid'], UPDATE_VISIBLE, $userid, $params['oldquestion']['userid']);
				break;


			case 'q_move':
				qa_create_event_for_q_user($params['postid'], $params['postid'], UPDATE_CATEGORY, $userid, $params['oldquestion']['userid']);
				qa_create_event_for_category($params['categoryid'], $params['postid'], UPDATE_CATEGORY, $userid);
				break;


			case 'a_edit':
				if ($params['contentchanged'])
					qa_create_event_for_q_user($params['parentid'], $params['postid'], UPDATE_CONTENT, $userid, $params['oldanswer']['userid']);
				break;


			case 'a_hide':
				if (isset($params['oldanswer']['userid']))
					qa_db_event_create_not_entity($params['oldanswer']['userid'], $params['parentid'], $params['postid'], UPDATE_VISIBLE, $userid);
				break;


			case 'a_reshow':
				qa_create_event_for_q_user($params['parentid'], $params['postid'], UPDATE_VISIBLE, $userid, $params['oldanswer']['userid']);
				break;


			case 'c_edit':
				if ($params['contentchanged'])
					qa_create_event_for_q_user($params['questionid'], $params['postid'], UPDATE_CONTENT, $userid, $params['oldcomment']['userid']);
				break;


			case 'a_to_c':
				if ($params['contentchanged'])
					qa_create_event_for_q_user($params['questionid'], $params['postid'], UPDATE_CONTENT, $userid, $params['oldanswer']['userid']);
				else
					qa_create_event_for_q_user($params['questionid'], $params['postid'], UPDATE_TYPE, $userid, $params['oldanswer']['userid']);
				break;


			case 'c_hide':
				if (isset($params['oldcomment']['userid']))
					qa_db_event_create_not_entity($params['oldcomment']['userid'], $params['questionid'], $params['postid'], UPDATE_VISIBLE, $userid);
				break;


			case 'c_reshow':
				qa_create_event_for_q_user($params['questionid'], $params['postid'], UPDATE_VISIBLE, $userid, $params['oldcomment']['userid']);
				break;
		}
	}
}
