<?php

/*
	Theme layer class for viewing voters and flaggers
*/

class html_theme_layer extends html_theme_base
{
	private $voters_flaggers_queue = [];
	private $voters_flaggers_cache = [];


	// Collect up all required postids for the entire page to save DB queries - common case where whole page output

	public function main()
	{
		foreach ($this->content as $key => $part) {
			if (strpos($key, 'q_list') === 0) {
				if (isset($part['qs']))
					$this->queue_raw_posts_voters_flaggers($part['qs']);

			} elseif (strpos($key, 'q_view') === 0) {
				$this->queue_post_voters_flaggers($part['raw']);
				$this->queue_raw_posts_voters_flaggers($part['c_list']['cs']);

			} elseif (strpos($key, 'a_list') === 0) {
				if (!empty($part)) {
					$this->queue_raw_posts_voters_flaggers($part['as']);

					foreach ($part['as'] as $a_item) {
						if (isset($a_item['c_list']['cs']))
							$this->queue_raw_posts_voters_flaggers($a_item['c_list']['cs']);
					}
				}
			}
		}

		parent::main();
	}


	// Other functions which also collect up required postids for lists to save DB queries - helps with widget output and Ajax calls

	public function q_list_items($q_items)
	{
		$this->queue_raw_posts_voters_flaggers($q_items);

		parent::q_list_items($q_items);
	}

	public function a_list_items($a_items)
	{
		$this->queue_raw_posts_voters_flaggers($a_items);

		parent::a_list_items($a_items);
	}

	public function c_list_items($c_items)
	{
		$this->queue_raw_posts_voters_flaggers($c_items);

		parent::c_list_items($c_items);
	}


	// Actual output of the voters and flaggers

	public function vote_count($post)
	{
		$postid = isset($post['vote_opostid']) && $post['vote_opostid'] ? $post['raw']['opostid'] : $post['raw']['postid'];
		$votersflaggers = $this->get_post_voters_flaggers($post['raw'], $postid);

		if (isset($votersflaggers)) {
			$uphandles = array();
			$downhandles = array();

			foreach ($votersflaggers as $voterflagger) {
				if ($voterflagger['vote'] != 0) {
					$newflagger = qa_html($voterflagger['handle']);
					if ($voterflagger['vote'] > 0)
						$uphandles[] = $newflagger;
					else  // if ($voterflagger['vote'] < 0)
						$downhandles[] = $newflagger;
				}
			}

			$tooltip = trim(
				(empty($uphandles) ? '' : '&uarr; ' . implode(', ', $uphandles)) . "\n\n" .
				(empty($downhandles) ? '' : '&darr; ' . implode(', ', $downhandles))
			);

			$post['vote_count_tags'] = sprintf('%s title="%s"', isset($post['vote_count_tags']) ? $post['vote_count_tags'] : '', $tooltip);
		}

		parent::vote_count($post);
	}

	public function post_meta_flags($post, $class)
	{
		if (isset($post['raw']['opostid']))
			$postid = $post['raw']['opostid'];
		elseif (isset($post['raw']['postid']))
			$postid = $post['raw']['postid'];

		$flaggers = array();

		if (isset($postid)) {
			$votersflaggers = $this->get_post_voters_flaggers($post, $postid);

			if (isset($votersflaggers)) {
				foreach ($votersflaggers as $voterflagger) {
					if ($voterflagger['flag'] > 0)
						$flaggers[] = qa_html($voterflagger['handle']);
				}
			}
		}

		if (!empty($flaggers))
			$this->output('<span title="&#9873; ' . implode(', ', $flaggers) . '">');

		parent::post_meta_flags($post, $class);

		if (!empty($flaggers))
			$this->output('</span>');
	}


	// Utility functions for this layer

	private function queue_post_voters_flaggers($post)
	{
		if (!qa_user_post_permit_error('permit_view_voters_flaggers', $post)) {
			$postkeys = array('postid', 'opostid');
			foreach ($postkeys as $key) {
				if (isset($post[$key]) && !isset($this->voters_flaggers_cache[$post[$key]]))
					$this->voters_flaggers_queue[$post[$key]] = true;
			}
		}
	}

	private function queue_raw_posts_voters_flaggers($posts)
	{
		if (is_array($posts)) {
			foreach ($posts as $post) {
				if (isset($post['raw']))
					$this->queue_post_voters_flaggers($post['raw']);
			}
		}
	}

	private function retrieve_queued_voters_flaggers()
	{
		if (count($this->voters_flaggers_queue)) {
			require_once INCLUDE_DIR . 'db/votes.php';

			$postids = array_keys($this->voters_flaggers_queue);

			foreach ($postids as $postid) {
				$this->voters_flaggers_cache[$postid] = array();
			}

			$newvotersflaggers = qa_db_uservoteflag_posts_get($postids);

			if (FINAL_EXTERNAL_USERS) {
				$keyuserids = array();
				foreach ($newvotersflaggers as $voterflagger) {
					$keyuserids[$voterflagger['userid']] = true;
				}

				$useridhandles = qa_get_public_from_userids(array_keys($keyuserids));
				foreach ($newvotersflaggers as $index => $voterflagger) {
					$newvotersflaggers[$index]['handle'] = isset($useridhandles[$voterflagger['userid']]) ? $useridhandles[$voterflagger['userid']] : null;
				}
			}

			foreach ($newvotersflaggers as $voterflagger) {
				$this->voters_flaggers_cache[$voterflagger['postid']][] = $voterflagger;
			}

			$this->voters_flaggers_queue = [];
		}
	}

	private function get_post_voters_flaggers($post, $postid)
	{
		require_once INCLUDE_DIR . 'util/sort.php';

		if (!isset($this->voters_flaggers_cache[$postid])) {
			$this->queue_post_voters_flaggers($post);
			$this->retrieve_queued_voters_flaggers();
		}

		$votersflaggers = isset($this->voters_flaggers_cache[$postid]) ? $this->voters_flaggers_cache[$postid] : null;

		if (isset($votersflaggers))
			qa_sort_by($votersflaggers, 'handle');

		return $votersflaggers;
	}
}
