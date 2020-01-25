-- Tabloid Install SQL

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `blobs` (
  `blobid` bigint unsigned NOT NULL,
  `format` varchar(20) NOT NULL,
  `content` mediumblob,
  `filename` varchar(500) DEFAULT NULL,
  `userid` int unsigned DEFAULT NULL,
  `cookieid` bigint unsigned DEFAULT NULL,
  `createip` varbinary(16) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`blobid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `cache` (
  `type` char(8) NOT NULL,
  `cacheid` bigint unsigned NOT NULL DEFAULT '0',
  `content` mediumblob NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastread` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`type`,`cacheid`),
  KEY `lastread` (`lastread`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `categories` (
  `categoryid` int unsigned NOT NULL AUTO_INCREMENT,
  `parentid` int unsigned DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `tags` varchar(200) NOT NULL,
  `content` varchar(500) NOT NULL DEFAULT '',
  `qcount` int unsigned NOT NULL DEFAULT '0',
  `position` smallint unsigned NOT NULL,
  `backpath` varchar(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`categoryid`),
  UNIQUE KEY `parentid` (`parentid`,`tags`),
  UNIQUE KEY `parentid_2` (`parentid`,`position`),
  KEY `backpath` (`backpath`(200))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `categorymetas` (
  `categoryid` int unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` varchar(500) NOT NULL,
  PRIMARY KEY (`categoryid`,`title`),
  CONSTRAINT `categorymetas_ibfk_1` FOREIGN KEY (`categoryid`) REFERENCES `categories` (`categoryid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `contentwords` (
  `postid` int unsigned NOT NULL,
  `wordid` int unsigned NOT NULL,
  `count` int unsigned DEFAULT '0',
  `type` enum('Q','A','C','NOTE','POST','LINK','REVIEW','SURVEY','HTML','JS','EMBED','VIDEO','BANNER','ADS') NOT NULL,
  `questionid` int unsigned NOT NULL,
  KEY `postid` (`postid`),
  KEY `wordid` (`wordid`),
  CONSTRAINT `contentwords_ibfk_1` FOREIGN KEY (`postid`) REFERENCES `posts` (`postid`) ON DELETE CASCADE,
  CONSTRAINT `contentwords_ibfk_2` FOREIGN KEY (`wordid`) REFERENCES `words` (`wordid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `cookies` (
  `cookieid` bigint unsigned NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `createip` varbinary(16) NOT NULL,
  `written` datetime DEFAULT NULL,
  `writeip` varbinary(16) DEFAULT NULL,
  PRIMARY KEY (`cookieid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `extlog` (
  `type` varchar(50) NOT NULL,
  `extid` int unsigned NOT NULL,
  `code` int unsigned DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `stamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx` (`type`,`extid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `extposts` (
  `type` varchar(50) NOT NULL,
  `extid` int unsigned NOT NULL,
  `postid` int unsigned NOT NULL,
  `extthread` int unsigned DEFAULT NULL,
  `translator` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`type`,`extid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `extusers` (
  `extid` int unsigned NOT NULL,
  `userid` int unsigned NOT NULL,
  PRIMARY KEY (`extid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `iplimits` (
  `ip` varbinary(16) NOT NULL,
  `action` char(1) NOT NULL,
  `period` int unsigned NOT NULL,
  `count` smallint unsigned NOT NULL,
  UNIQUE KEY `ip` (`ip`,`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `messages` (
  `messageid` int unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('PUBLIC','PRIVATE') NOT NULL DEFAULT 'PRIVATE',
  `fromuserid` int unsigned DEFAULT NULL,
  `touserid` int unsigned DEFAULT NULL,
  `fromhidden` tinyint unsigned NOT NULL DEFAULT '0',
  `tohidden` tinyint unsigned NOT NULL DEFAULT '0',
  `content` varchar(1000) NOT NULL,
  `format` varchar(20) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`messageid`),
  KEY `type` (`type`,`fromuserid`,`touserid`,`created`),
  KEY `touserid` (`touserid`,`type`,`created`),
  KEY `fromhidden` (`fromhidden`),
  KEY `tohidden` (`tohidden`),
  KEY `qa_messages_ibfk_1` (`fromuserid`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`fromuserid`) REFERENCES `users` (`userid`) ON DELETE SET NULL,
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`touserid`) REFERENCES `users` (`userid`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `migration_versions` (
  `version` varchar(500) NOT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `options` (
  `title` varchar(100) NOT NULL,
  `content` varchar(1000) NOT NULL,
  PRIMARY KEY (`title`),
  KEY `options` (`title`,`content`(200))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `options` (`title`, `content`) VALUES
	('allow_anonymous_naming', '1'),
	('allow_change_usernames', '0'),
	('allow_close_own_questions', '1'),
	('allow_close_questions', '0'),
	('allow_login_email_only', '0'),
	('allow_multi_answers', '1'),
	('allow_no_category', '0'),
	('allow_no_sub_category', '0'),
	('allow_private_messages', '0'),
	('allow_self_answer', '1'),
	('allow_user_walls', '0'),
	('allow_view_q_bots', '1'),
	('avatar_allow_gravatar', '1'),
	('avatar_allow_upload', '1'),
	('avatar_default_blobid', ''),
	('avatar_default_height', ''),
	('avatar_default_show', '0'),
	('avatar_default_width', ''),
	('avatar_message_list_size', '20'),
	('avatar_profile_size', '200'),
	('avatar_q_list_size', '0'),
	('avatar_q_page_a_size', '40'),
	('avatar_q_page_c_size', '20'),
	('avatar_q_page_q_size', '50'),
	('avatar_store_size', '400'),
	('avatar_users_size', '30'),
	('block_bad_usernames', 'anonymous'),
	('block_bad_words', ''),
	('block_ips_write', ''),
	('cache_acount', '0'),
	('cache_ccount', '0'),
	('cache_flaggedcount', ''),
	('cache_qcount', '0'),
	('cache_queuedcount', ''),
	('cache_tagcount', '0'),
	('cache_uapprovecount', '0'),
	('cache_unaqcount', '0'),
	('cache_unselqcount', '0'),
	('cache_unupaqcount', '0'),
	('cache_userpointscount', '0'),
	('caching_catwidget_time', '30'),
	('caching_driver', 'filesystem'),
	('caching_enabled', '0'),
	('caching_q_start', '7'),
	('caching_q_time', '30'),
	('captcha_module', 'reCAPTCHA'),
	('captcha_on_anon_post', '1'),
	('captcha_on_feedback', '1'),
	('captcha_on_register', '1'),
	('captcha_on_reset_password', '1'),
	('captcha_on_unapproved', '0'),
	('columns_tags', '3'),
	('columns_users', '2'),
	('comment_on_as', '1'),
	('comment_on_qs', '1'),
	('confirm_user_emails', '1'),
	('confirm_user_required', '0'),
	('custom_answer', ''),
	('custom_ask', ''),
	('custom_comment', ''),
	('custom_footer', ''),
	('custom_header', ''),
	('custom_home_content', ''),
	('custom_home_heading', ''),
	('custom_in_head', ''),
	('custom_register', ''),
	('custom_sidepanel', ''),
	('custom_welcome', ''),
	('db_version', '67'),
	('do_ask_check_qs', '0'),
	('do_close_on_select', '0'),
	('do_complete_tags', '0'),
	('do_count_q_views', '0'),
	('do_example_tags', '0'),
	('editor_for_as', 'WYSIWYG Editor'),
	('editor_for_cs', ''),
	('editor_for_qs', 'WYSIWYG Editor'),
	('email_privacy', 'Your e-mail will be private'),
	('enabled_plugins', 'event-logger;recaptcha-captcha;wysiwyg-editor'),
	('event_logger_to_database', ''),
	('event_logger_to_files', ''),
	('extra_field_active', '0'),
	('extra_field_display', '0'),
	('extra_field_label', ''),
	('extra_field_prompt', ''),
	('facebook_app_id', ''),
	('feedback_email', ''),
	('feedback_enabled', '1'),
	('feed_for_activity', '0'),
	('feed_for_hot', '0'),
	('feed_for_qa', '0'),
	('feed_for_questions', '0'),
	('feed_for_search', '0'),
	('feed_for_tag_qs', '0'),
	('feed_for_unanswered', '0'),
	('feed_full_text', '1'),
	('feed_number_items', '50'),
	('feed_per_category', '0'),
	('flagging_hide_after', '5'),
	('flagging_notify_every', '2'),
	('flagging_notify_first', '1'),
	('flagging_of_posts', '1'),
	('follow_on_as', '1'),
	('form_security_salt', ''),
	('from_email', ''),
	('home_description', ''),
	('hot_weight_answers', '100'),
	('hot_weight_a_age', '100'),
	('hot_weight_q_age', '100'),
	('hot_weight_views', '100'),
	('hot_weight_votes', '100'),
	('links_in_new_window', '1'),
	('logo_height', ''),
	('logo_show', ''),
	('logo_url', ''),
	('logo_width', ''),
	('mailing_body', ''),
	('mailing_enabled', ''),
	('mailing_from_email', 'tabloid@localhost'),
	('mailing_from_name', 'Tabloid'),
	('mailing_last_userid', ''),
	('mailing_per_minute', '500'),
	('mailing_subject', 'A message from Tabloid'),
	('match_ask_check_qs', '3'),
	('match_example_tags', '3'),
	('match_related_qs', '3'),
	('max_len_q_title', '800'),
	('max_num_q_tags', '10'),
	('max_rate_ip_as', '50'),
	('max_rate_ip_cs', '40'),
	('max_rate_ip_flags', '10'),
	('max_rate_ip_logins', '20'),
	('max_rate_ip_messages', '10'),
	('max_rate_ip_qs', '20'),
	('max_rate_ip_registers', '5'),
	('max_rate_ip_uploads', '20'),
	('max_rate_ip_votes', '600'),
	('max_rate_user_as', '25'),
	('max_rate_user_cs', '20'),
	('max_rate_user_flags', '5'),
	('max_rate_user_messages', '5'),
	('max_rate_user_qs', '10'),
	('max_rate_user_uploads', '10'),
	('max_rate_user_votes', '300'),
	('max_store_user_updates', '50'),
	('minify_html', '1'),
	('min_len_a_content', '10'),
	('min_len_c_content', '10'),
	('min_len_q_content', '0'),
	('min_len_q_title', '10'),
	('min_num_q_tags', '0'),
	('moderate_by_points', '0'),
	('moderate_edited_again', '0'),
	('moderate_notify_admin', '1'),
	('moderate_points_limit', '150'),
	('moderate_unapproved', '0'),
	('moderate_update_time', '1'),
	('moderate_users', '0'),
	('mouseover_content_on', ''),
	('nav_activity', '0'),
	('nav_ask', '1'),
	('nav_categories', '0'),
	('nav_home', ''),
	('nav_hot', '1'),
	('nav_qa_is_home', '0'),
	('nav_qa_not_home', '1'),
	('nav_questions', '1'),
	('nav_tags', '1'),
	('nav_unanswered', '0'),
	('nav_users', '1'),
	('neat_urls', '0'),
	('notice_visitor', ''),
	('notice_welcome', ''),
	('notify_admin_q_post', '0'),
	('notify_users_default', '0'),
	('pages_prev_next', '5'),
	('page_size_activity', '25'),
	('page_size_ask_check_qs', '5'),
	('page_size_ask_tags', '5'),
	('page_size_home', '25'),
	('page_size_hot_qs', '25'),
	('page_size_pms', '10'),
	('page_size_qs', '25'),
	('page_size_q_as', '10'),
	('page_size_related_qs', '10'),
	('page_size_search', '10'),
	('page_size_tags', '50'),
	('page_size_tag_qs', '25'),
	('page_size_una_qs', '25'),
	('page_size_users', '50'),
	('page_size_wall', '10'),
	('permit_anon_view_ips', '70'),
	('permit_anon_view_ips_points', ''),
	('permit_close_q', '70'),
	('permit_close_q_points', ''),
	('permit_delete_hidden', '40'),
	('permit_delete_hidden_points', ''),
	('permit_edit_a', '70'),
	('permit_edit_a_points', ''),
	('permit_edit_c', '70'),
	('permit_edit_c_points', ''),
	('permit_edit_q', '70'),
	('permit_edit_q_points', ''),
	('permit_edit_silent', '40'),
	('permit_edit_silent_points', ''),
	('permit_flag', '110'),
	('permit_flag_points', ''),
	('permit_hide_show', '70'),
	('permit_hide_show_points', ''),
	('permit_moderate', '100'),
	('permit_moderate_points', ''),
	('permit_post_a', '110'),
	('permit_post_a_points', ''),
	('permit_post_c', '110'),
	('permit_post_c_points', ''),
	('permit_post_q', '110'),
	('permit_post_q_points', ''),
	('permit_post_wall', '110'),
	('permit_post_wall_points', ''),
	('permit_retag_cat', '70'),
	('permit_retag_cat_points', ''),
	('permit_select_a', '100'),
	('permit_select_a_points', ''),
	('permit_view_new_users_page', '70'),
	('permit_view_new_users_page_points', ''),
	('permit_view_q_page', '150'),
	('permit_view_special_users_page', '40'),
	('permit_view_special_users_page_points', ''),
	('permit_view_voters_flaggers', '20'),
	('permit_view_voters_flaggers_points', ''),
	('permit_vote_a', '110'),
	('permit_vote_a_points', ''),
	('permit_vote_c', '120'),
	('permit_vote_c_points', ''),
	('permit_vote_down', '110'),
	('permit_vote_down_points', ''),
	('permit_vote_q', '110'),
	('permit_vote_q_points', ''),
	('points_a_selected', '30'),
	('points_a_voted_max_gain', '20'),
	('points_a_voted_max_loss', '5'),
	('points_base', '100'),
	('points_c_voted_max_gain', '10'),
	('points_c_voted_max_loss', '3'),
	('points_multiple', '10'),
	('points_per_a_voted', ''),
	('points_per_a_voted_down', '2'),
	('points_per_a_voted_up', '2'),
	('points_per_c_voted_down', '0'),
	('points_per_c_voted_up', '0'),
	('points_per_q_voted', ''),
	('points_per_q_voted_down', '1'),
	('points_per_q_voted_up', '1'),
	('points_post_a', '4'),
	('points_post_q', '2'),
	('points_q_voted_max_gain', '10'),
	('points_q_voted_max_loss', '3'),
	('points_select_a', '3'),
	('points_to_titles', ''),
	('points_vote_down_a', '1'),
	('points_vote_down_q', '1'),
	('points_vote_on_a', ''),
	('points_vote_on_q', ''),
	('points_vote_up_a', '1'),
	('points_vote_up_q', '1'),
	('q_urls_remove_accents', '0'),
	('q_urls_title_length', '100'),
	('recalc_hotness_q_view', '1'),
	('recaptcha_private_key', ''),
	('recaptcha_public_key', ''),
	('register_notify_admin', '0'),
	('register_terms', 'I agree to the Tabloid Terms &amp; Conditions and Privacy Policy'),
	('search_module', ''),
	('show_a_c_links', '1'),
	('show_a_form_immediate', 'never'),
	('show_compact_numbers', '1'),
	('show_custom_answer', '0'),
	('show_custom_ask', '0'),
	('show_custom_comment', '0'),
	('show_custom_footer', ''),
	('show_custom_header', ''),
	('show_custom_home', ''),
	('show_custom_in_head', ''),
	('show_custom_register', '0'),
	('show_custom_sidebar', '1'),
	('show_custom_sidepanel', ''),
	('show_custom_welcome', '0'),
	('show_c_reply_buttons', '1'),
	('show_fewer_cs_count', '5'),
	('show_fewer_cs_from', '10'),
	('show_full_date_days', '7'),
	('show_home_description', ''),
	('show_message_history', '1'),
	('show_notice_visitor', '0'),
	('show_notice_welcome', '0'),
	('show_post_update_meta', '1'),
	('show_register_terms', '0'),
	('show_selected_first', '1'),
	('show_url_links', '1'),
	('show_user_points', '0'),
	('show_user_titles', '1'),
	('show_view_counts', '0'),
	('show_view_count_q_page', '0'),
	('show_when_created', '1'),
	('site_language', ''),
	('site_maintenance', '0'),
	('site_text_direction', 'ltr'),
	('site_theme', 'zen'),
	('site_theme_mobile', 'zen'),
	('site_title', 'Tabloid'),
	('site_url', 'http://localhost/'),
	('smtp_active', '0'),
	('smtp_address', ''),
	('smtp_authenticate', '0'),
	('smtp_password', ''),
	('smtp_port', '25'),
	('smtp_secure', ''),
	('smtp_username', ''),
	('sort_answers_by', 'votes'),
	('suspend_register_users', '0'),
	('tags_or_categories', 'tc'),
	('tag_separator_comma', '0'),
	('use_microdata', '1'),
	('votes_separated', '0'),
	('voting_on_as', '1'),
	('voting_on_cs', '1'),
	('voting_on_qs', '1'),
	('voting_on_q_page_only', '1'),
	('wysiwyg_editor_upload_images', ''),
	('wysiwyg_editor_upload_max_size', '1048576');

CREATE TABLE IF NOT EXISTS `pages` (
  `pageid` smallint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `nav` char(1) NOT NULL,
  `position` smallint unsigned NOT NULL,
  `flags` tinyint unsigned NOT NULL,
  `permit` tinyint unsigned DEFAULT NULL,
  `tags` varchar(200) NOT NULL,
  `heading` varchar(500) DEFAULT NULL,
  `content` longtext,
  PRIMARY KEY (`pageid`),
  UNIQUE KEY `position` (`position`),
  KEY `tags` (`tags`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `postmetas` (
  `postid` int unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` varchar(1000) NOT NULL,
  PRIMARY KEY (`postid`,`title`),
  CONSTRAINT `postmetas_ibfk_1` FOREIGN KEY (`postid`) REFERENCES `posts` (`postid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `posts` (
  `postid` int unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('Q','A','C','Q_HIDDEN','A_HIDDEN','C_HIDDEN','Q_QUEUED','A_QUEUED','C_QUEUED','NOTE','POST','LINK','REVIEW','SURVEY','HTML','JS','EMBED','VIDEO','BANNER','ADS') COLLATE utf8mb4_unicode_ci NOT NULL,
  `parentid` int unsigned DEFAULT NULL,
  `categoryid` int unsigned DEFAULT NULL,
  `catidpath1` int unsigned DEFAULT NULL,
  `catidpath2` int unsigned DEFAULT NULL,
  `catidpath3` int unsigned DEFAULT NULL,
  `acount` smallint unsigned NOT NULL DEFAULT '0',
  `amaxvote` smallint unsigned NOT NULL DEFAULT '0',
  `selchildid` int unsigned DEFAULT NULL,
  `closedbyid` int unsigned DEFAULT NULL,
  `userid` int unsigned DEFAULT NULL,
  `cookieid` bigint unsigned DEFAULT NULL,
  `createip` varbinary(16) DEFAULT NULL,
  `lastuserid` int unsigned DEFAULT NULL,
  `lastip` varbinary(16) DEFAULT NULL,
  `upvotes` smallint unsigned NOT NULL DEFAULT '0',
  `downvotes` smallint unsigned NOT NULL DEFAULT '0',
  `netvotes` smallint NOT NULL DEFAULT '0',
  `lastviewip` varbinary(16) DEFAULT NULL,
  `views` int unsigned NOT NULL DEFAULT '0',
  `hotness` float DEFAULT NULL,
  `flagcount` tinyint unsigned NOT NULL DEFAULT '0',
  `format` varchar(20) NOT NULL DEFAULT '',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT NULL,
  `updatetype` char(1) DEFAULT NULL,
  `title` varchar(500) DEFAULT NULL,
  `lead` varchar(500) DEFAULT NULL,  
  `content` mediumtext,
  `tags` varchar(200) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `notify` varchar(100) DEFAULT NULL,
  `extvotes` smallint NOT NULL DEFAULT '0',  
  `image` varchar(500) DEFAULT NULL,
  `popularity` smallint DEFAULT '0',
  `promo` varchar(100) DEFAULT NULL,
  `promo_start` datetime DEFAULT NULL,
  `promo_end` datetime DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `rating` tinyint DEFAULT NULL,  
  `sort` varchar(20) GENERATED ALWAYS AS (concat((case when isnull(`categoryid`) then '0' else '1' end),date_format(`created`, '%Y%m%d%H%i%s'))) STORED,
  PRIMARY KEY (`postid`),
  KEY `type` (`type`,`created`),
  KEY `type_2` (`type`,`acount`,`created`),
  KEY `type_4` (`type`,`netvotes`,`created`),
  KEY `type_5` (`type`,`views`,`created`),
  KEY `type_6` (`type`,`hotness`),
  KEY `type_7` (`type`,`amaxvote`,`created`),
  KEY `parentid` (`parentid`,`type`),
  KEY `userid` (`userid`,`type`,`created`),
  KEY `selchildid` (`selchildid`,`type`,`created`),
  KEY `closedbyid` (`closedbyid`),
  KEY `catidpath1` (`catidpath1`,`type`,`created`),
  KEY `catidpath2` (`catidpath2`,`type`,`created`),
  KEY `catidpath3` (`catidpath3`,`type`,`created`),
  KEY `categoryid` (`categoryid`,`type`,`created`),
  KEY `createip` (`createip`,`created`),
  KEY `updated` (`updated`,`type`),
  KEY `flagcount` (`flagcount`,`created`,`type`),
  KEY `catidpath1_2` (`catidpath1`,`updated`,`type`),
  KEY `catidpath2_2` (`catidpath2`,`updated`,`type`),
  KEY `catidpath3_2` (`catidpath3`,`updated`,`type`),
  KEY `categoryid_2` (`categoryid`,`updated`,`type`),
  KEY `lastuserid` (`lastuserid`,`updated`,`type`),
  KEY `lastip` (`lastip`,`updated`,`type`),
  KEY `created` (`created`),
  KEY `hotness` (`hotness`),
  KEY `sort_idx` (`sort`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE SET NULL,
  CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`parentid`) REFERENCES `posts` (`postid`),
  CONSTRAINT `posts_ibfk_4` FOREIGN KEY (`closedbyid`) REFERENCES `posts` (`postid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `posttags` (
  `postid` int unsigned NOT NULL,
  `wordid` int unsigned NOT NULL,
  `postcreated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `postid` (`postid`),
  KEY `wordid` (`wordid`,`postcreated`),
  CONSTRAINT `posttags_ibfk_1` FOREIGN KEY (`postid`) REFERENCES `posts` (`postid`) ON DELETE CASCADE,
  CONSTRAINT `posttags_ibfk_2` FOREIGN KEY (`wordid`) REFERENCES `words` (`wordid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `sharedevents` (
  `entitytype` char(1) NOT NULL,
  `entityid` int unsigned NOT NULL,
  `questionid` int unsigned NOT NULL,
  `lastpostid` int unsigned NOT NULL,
  `updatetype` char(1) DEFAULT NULL,
  `lastuserid` int unsigned DEFAULT NULL,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `entitytype` (`entitytype`,`entityid`,`updated`),
  KEY `questionid` (`questionid`,`entitytype`,`entityid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tagmetas` (
  `tag` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL,
  `content` varchar(1000) NOT NULL,
  PRIMARY KEY (`tag`,`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tagwords` (
  `postid` int unsigned NOT NULL,
  `wordid` int unsigned NOT NULL,
  KEY `postid` (`postid`),
  KEY `wordid` (`wordid`),
  CONSTRAINT `tagwords_ibfk_1` FOREIGN KEY (`postid`) REFERENCES `posts` (`postid`) ON DELETE CASCADE,
  CONSTRAINT `tagwords_ibfk_2` FOREIGN KEY (`wordid`) REFERENCES `words` (`wordid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `titlewords` (
  `postid` int unsigned NOT NULL,
  `wordid` int unsigned NOT NULL,
  KEY `postid` (`postid`),
  KEY `wordid` (`wordid`),
  CONSTRAINT `titlewords_ibfk_1` FOREIGN KEY (`postid`) REFERENCES `posts` (`postid`) ON DELETE CASCADE,
  CONSTRAINT `titlewords_ibfk_2` FOREIGN KEY (`wordid`) REFERENCES `words` (`wordid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(200) NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_EMAIL` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `userevents` (
  `userid` int unsigned NOT NULL,
  `entitytype` char(1) NOT NULL,
  `entityid` int unsigned NOT NULL,
  `questionid` int unsigned NOT NULL,
  `lastpostid` int unsigned NOT NULL,
  `updatetype` char(1) DEFAULT NULL,
  `lastuserid` int unsigned DEFAULT NULL,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `userid` (`userid`,`updated`),
  KEY `questionid` (`questionid`,`userid`),
  CONSTRAINT `userevents_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `userfavorites` (
  `userid` int unsigned NOT NULL,
  `entitytype` char(1) NOT NULL,
  `entityid` int unsigned NOT NULL,
  `nouserevents` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`userid`,`entitytype`,`entityid`),
  KEY `userid` (`userid`,`nouserevents`),
  KEY `entitytype` (`entitytype`,`entityid`,`nouserevents`),
  CONSTRAINT `userfavorites_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `userfields` (
  `fieldid` smallint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `content` varchar(500) DEFAULT NULL,
  `position` smallint unsigned NOT NULL,
  `flags` tinyint unsigned NOT NULL,
  `permit` tinyint unsigned DEFAULT NULL,
  PRIMARY KEY (`fieldid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;


INSERT INTO `userfields` (`fieldid`, `title`, `content`, `position`, `flags`, `permit`) VALUES
	(1, 'Name', NULL, 1, 0, 150),
	(2, 'Location', NULL, 2, 0, 150),
	(3, 'Website', NULL, 3, 2, 150),
	(4, 'About', NULL, 4, 1, 150);

CREATE TABLE IF NOT EXISTS `userlevels` (
  `userid` int unsigned NOT NULL,
  `entitytype` char(1) NOT NULL,
  `entityid` int unsigned NOT NULL,
  `level` tinyint unsigned DEFAULT NULL,
  UNIQUE KEY `userid` (`userid`,`entitytype`,`entityid`),
  KEY `entitytype` (`entitytype`,`entityid`),
  CONSTRAINT `userlevels_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `userlimits` (
  `userid` int unsigned NOT NULL,
  `action` char(1) NOT NULL,
  `period` int unsigned NOT NULL,
  `count` smallint unsigned NOT NULL,
  UNIQUE KEY `userid` (`userid`,`action`),
  CONSTRAINT `userlimits_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Dumping structure for table tabloid.userlogins
CREATE TABLE IF NOT EXISTS `userlogins` (
  `userid` int unsigned NOT NULL,
  `source` varchar(16) NOT NULL,
  `identifier` varbinary(1024) NOT NULL,
  `identifiermd5` binary(16) NOT NULL,
  KEY `source` (`source`,`identifiermd5`),
  KEY `userid` (`userid`),
  CONSTRAINT `userlogins_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `usermetas` (
  `userid` int unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` varchar(1000) NOT NULL,
  PRIMARY KEY (`userid`,`title`),
  CONSTRAINT `usermetas_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `usernotices` (
  `noticeid` int unsigned NOT NULL AUTO_INCREMENT,
  `userid` int unsigned NOT NULL,
  `content` varchar(1000) NOT NULL,
  `format` varchar(20) NOT NULL,
  `tags` varchar(200) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`noticeid`),
  KEY `userid` (`userid`,`created`),
  CONSTRAINT `usernotices_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `userpoints` (
  `userid` int unsigned NOT NULL,
  `points` int NOT NULL DEFAULT '0',
  `qposts` mediumint NOT NULL DEFAULT '0',
  `aposts` mediumint NOT NULL DEFAULT '0',
  `cposts` mediumint NOT NULL DEFAULT '0',
  `aselects` mediumint NOT NULL DEFAULT '0',
  `aselecteds` mediumint NOT NULL DEFAULT '0',
  `qupvotes` mediumint NOT NULL DEFAULT '0',
  `qdownvotes` mediumint NOT NULL DEFAULT '0',
  `aupvotes` mediumint NOT NULL DEFAULT '0',
  `adownvotes` mediumint NOT NULL DEFAULT '0',
  `cupvotes` mediumint NOT NULL DEFAULT '0',
  `cdownvotes` mediumint NOT NULL DEFAULT '0',
  `qvoteds` int NOT NULL DEFAULT '0',
  `avoteds` int NOT NULL DEFAULT '0',
  `cvoteds` int NOT NULL DEFAULT '0',
  `upvoteds` int NOT NULL DEFAULT '0',
  `downvoteds` int NOT NULL DEFAULT '0',
  `bonus` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`userid`),
  KEY `points` (`points`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `userprofile` (
  `userid` int unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` varchar(1000) NOT NULL,
  UNIQUE KEY `userid` (`userid`,`title`),
  CONSTRAINT `userprofile_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO `userprofile` (`userid`, `title`, `content`) VALUES
	(1, 'About', ''),
	(1, 'Location', ''),
	(1, 'Name', ''),
	(1, 'Website', '');

CREATE TABLE IF NOT EXISTS `users` (
  `userid` int unsigned NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `createip` varbinary(16) DEFAULT NULL,
  `email` varchar(200) NOT NULL,
  `handle` varchar(200) NOT NULL,
  `avatarblobid` bigint unsigned DEFAULT NULL,
  `avatarwidth` smallint unsigned DEFAULT NULL,
  `avatarheight` smallint unsigned DEFAULT NULL,
  `passsalt` binary(16) DEFAULT NULL,
  `passcheck` binary(20) DEFAULT NULL,
  `passhash` varchar(500) DEFAULT NULL,
  `level` tinyint unsigned NOT NULL DEFAULT '0',
  `loggedin` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `loginip` varbinary(16) DEFAULT NULL,
  `written` datetime DEFAULT NULL,
  `writeip` varbinary(16) DEFAULT NULL,
  `emailcode` char(8) NOT NULL DEFAULT '',
  `sessioncode` char(8) NOT NULL DEFAULT '',
  `sessionsource` varchar(16) DEFAULT '',
  `flags` smallint unsigned NOT NULL DEFAULT '0',
  `wallposts` mediumint NOT NULL DEFAULT '0',
  PRIMARY KEY (`userid`),
  KEY `email` (`email`),
  KEY `handle` (`handle`),
  KEY `level` (`level`),
  KEY `created` (`created`,`level`,`flags`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=2;


INSERT INTO `users` (`userid`, `created`, `createip`, `email`, `handle`, `avatarblobid`, `avatarwidth`, `avatarheight`, `passsalt`, `passcheck`, `passhash`, `level`, `loggedin`, `loginip`, `written`, `writeip`, `emailcode`, `sessioncode`, `sessionsource`, `flags`, `wallposts`) VALUES
	(1, NOW(), _binary 0x00000000000000000000000000000001, 'tabloid@localhost', 'Tabloid', NULL, NULL, NULL, NULL, NULL, '$2y$10$2r9n/9qXrSx0iYqPwJk2VeNfOENUT7dxQXLIztwHfbzMnNjFQ//7e', 120, NOW(), _binary 0x00000000000000000000000000000001, NOW(), _binary 0x00000000000000000000000000000001, '', '', NULL, 0, 0);


CREATE TABLE IF NOT EXISTS `uservotes` (
  `postid` int unsigned NOT NULL,
  `userid` int unsigned NOT NULL,
  `vote` tinyint NOT NULL,
  `flag` tinyint NOT NULL,
  `votecreated` datetime DEFAULT NULL,
  `voteupdated` datetime DEFAULT NULL,
  UNIQUE KEY `userid` (`userid`,`postid`),
  KEY `postid` (`postid`),
  KEY `voted` (`votecreated`,`voteupdated`),
  CONSTRAINT `uservotes_ibfk_1` FOREIGN KEY (`postid`) REFERENCES `posts` (`postid`) ON DELETE CASCADE,
  CONSTRAINT `uservotes_ibfk_2` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `widgets` (
  `widgetid` smallint unsigned NOT NULL AUTO_INCREMENT,
  `place` char(2) NOT NULL,
  `position` smallint unsigned NOT NULL,
  `tags` varchar(200) NOT NULL,
  `title` varchar(100) NOT NULL,
  PRIMARY KEY (`widgetid`),
  UNIQUE KEY `position` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `words` (
  `wordid` int unsigned NOT NULL AUTO_INCREMENT,
  `word` varchar(50) NOT NULL,
  `titlecount` int unsigned NOT NULL DEFAULT '0',
  `contentcount` int unsigned NOT NULL DEFAULT '0',
  `tagwordcount` int unsigned NOT NULL DEFAULT '0',
  `tagcount` int unsigned NOT NULL DEFAULT '0',
  `comment` text,
  PRIMARY KEY (`wordid`),
  UNIQUE KEY `word_2` (`word`),
  UNIQUE KEY `words` (`word`),
  UNIQUE KEY `uniwords` (`word`),
  UNIQUE KEY `word_3` (`word`),
  UNIQUE KEY `word_4` (`word`),
  KEY `word` (`word`),
  KEY `tagcount` (`tagcount`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;