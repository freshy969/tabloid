<?php

/*
	Editor module class for WYSIWYG editor plugin
*/


class wysiwyg_ajax
{
	public function match_request($request)
	{
		return $request == 'wysiwyg-editor-ajax';
	}

	// Fix path to WYSIWYG editor smileys
	public function process_request($request)
	{
		require_once INCLUDE_DIR . 'app-posts.php';

		// smiley replacement regexes
		$rxSearch = '<(img|a)([^>]+)(src|href)="([^"]+)/wysiwyg-editor/plugins/smiley/images/([^"]+)"';
		$rxReplace = '<$1$2$3="$4/wysiwyg-editor/ckeditor/plugins/smiley/images/$5"';

		qa_suspend_event_reports(true); // avoid infinite loop

		// prevent race conditions
		$locks = array('posts', 'categories', 'users', 'users AS lastusers', 'userpoints', 'words', 'titlewords', 'contentwords', 'tagwords', 'words AS x', 'posttags', 'options');
		foreach ($locks as &$tbl)
			$tbl = '^'.$tbl.' WRITE';
		qa_db_query_sub('LOCK TABLES ' . implode(',', $locks));

		$sql =
			'SELECT postid, title, content FROM ^posts WHERE format="html" ' .
			'AND content LIKE "%/wysiwyg-editor/plugins/smiley/images/%" ' .
			'AND content RLIKE \'' . $rxSearch . '\' ' .
			'LIMIT 5';
		$result = qa_db_query_sub($sql);

		$numPosts = 0;
		while (($post=qa_db_read_one_assoc($result, true)) !== null) {
			$newcontent = preg_replace("#$rxSearch#", $rxReplace, $post['content']);
			qa_post_set_content($post['postid'], $post['title'], $newcontent);
			$numPosts++;
		}

		qa_db_query_raw('UNLOCK TABLES');
		qa_suspend_event_reports(false);

		echo $numPosts;
	}
}
