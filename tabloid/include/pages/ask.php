<?php

/*
	Controller for ask a question page
*/

require_once INCLUDE_DIR.'app/format.php';
require_once INCLUDE_DIR.'app/limits.php';
require_once INCLUDE_DIR.'db/selects.php';
require_once INCLUDE_DIR.'util/sort.php';


// Check whether this is a follow-on question and get some info we need from the database

$in = [];

$followpostid = qa_get('follow');
//$in['categoryid'] = qa_clicked('doask') ? qa_get_category_field_value('category') : qa_get('cat');
$in['categoryid'] = qa_clicked('doask') ? qa_post_text('category') : qa_get('cat');
$userid = qa_get_logged_in_userid();

if (qa_clicked('doask') && isset($_POST['type']))
	$in['type'] = $_POST['type'];
else
	$in['type'] = 'Q';

list($categories, $followanswer, $completetags) = qa_db_select_with_pending(
	qa_db_category_nav_selectspec($in['categoryid'], true),
	isset($followpostid) ? qa_db_full_post_selectspec($userid, $followpostid) : null,
	qa_db_popular_tags_selectspec(0, DB_RETRIEVE_COMPLETE_TAGS)
);

if (!isset($categories[$in['categoryid']])) {
	$in['categoryid'] = null;
}

if (@$followanswer['basetype'] != 'A') {
	$followanswer = null;
}


// Check for permission error

$permiterror = qa_user_maximum_permit_error('permit_post_q',LIMIT_QUESTIONS);

if ($permiterror) {
	$qa_content = qa_content_prepare();

	// The 'approve', 'login', 'confirm', 'limit', 'userblock', 'ipblock' permission errors are reported to the user here
	// The other option ('level') prevents the menu option being shown, in qa_content_prepare(...)

	switch ($permiterror) {
		case 'login':
			$qa_content['error'] = qa_insert_login_links(qa_lang_html('question/ask_must_login'), qa_request(), isset($followpostid) ? array('follow' => $followpostid) : null);
			break;

		case 'confirm':
			$qa_content['error'] = qa_insert_login_links(qa_lang_html('question/ask_must_confirm'), qa_request(), isset($followpostid) ? array('follow' => $followpostid) : null);
			break;

		case 'limit':
			$qa_content['error'] = qa_lang_html('question/ask_limit');
			break;

		case 'approve':
			$qa_content['error'] = strtr(qa_lang_html('question/ask_must_be_approved'), array(
				'^1' => '<a href="' . qa_path_html('account') . '">',
				'^2' => '</a>',
			));
			break;

		default:
			$qa_content['error'] = qa_lang_html('users/no_permission');
			break;
	}

	return $qa_content;
}


// Process input

$captchareason = qa_user_captcha_reason();

$in['title'] = qa_get_post_title('title'); // allow title and tags to be posted by an external form
$in['extra'] = qa_opt('extra_field_active') ? qa_post_text('extra') : null;

$in['type'] = qa_post_text('type');
$in['lead'] = qa_post_text('lead');
$in['image'] = qa_post_text('image');

if (qa_using_tags()) {
	$in['tags'] = qa_get_tags_field_value('tags');
}

// POST of form data

if (qa_clicked('doask')) {

	require_once INCLUDE_DIR.'app/post-create.php';
	require_once INCLUDE_DIR.'util/string.php';

	$categoryids = array_keys(qa_category_path($categories, @$in['categoryid']));
	$userlevel = qa_user_level_for_categories($categoryids);

    $errors = [];

	$in['name'] = qa_opt('allow_anonymous_naming') ? qa_post_text('name') : null;
	$in['notify'] = strlen(qa_post_text('notify')) > 0;
	$in['email'] = qa_post_text('email');
	$in['queued'] = qa_user_moderation_reason($userlevel) !== false;

    $in['image'] = qa_post_text('image'); // : $useraccount['handle'];
    //if (!isset($errors['handle']))
    //    qa_db_user_set($userid, 'handle', $inhandle);

    if (is_array(@$_FILES['file'])) {

        $fileerror = $_FILES['file']['error'];

        // Note if $_FILES['file']['error'] === 1 then upload_max_filesize has been exceeded
        if ($fileerror === 1)
            $errors['image'] = qa_lang('main/file_upload_limit_exceeded');
        elseif ($fileerror === 0 && $_FILES['file']['size'] > 0) {
            require_once INCLUDE_DIR . 'app/limits.php';
/*
            switch (qa_user_permit_error(null,LIMIT_UPLOADS)) {
                case 'limit':
                    $errors['avatar'] = qa_lang('main/upload_limit');
                    break;

                default:
                    $errors['avatar'] = qa_lang('users/no_permission');
                    break;

                case false:
                    qa_limits_increment($userid,LIMIT_UPLOADS);
                    $toobig = qa_image_file_too_big($_FILES['file']['tmp_name'], qa_opt('avatar_store_size'));

                    if ($toobig)
                        $errors['image'] = qa_lang_sub('main/image_too_big_x_pc', (int)($toobig * 100));
                    elseif (!qa_set_user_avatar($userid, file_get_contents($_FILES['file']['tmp_name']), $useraccount['avatarblobid']))
                        $errors['image'] = qa_lang_sub('main/image_not_read', implode(', ', qa_gd_image_formats()));
                    break;
            }
*/
        }  // There shouldn't be any need to catch any other error
    }

    if (is_array($_FILES) && count($_FILES)) {

        require_once INCLUDE_DIR . 'app/upload.php';

        $onlyImage = true;
        $created = (new DateTime())->format("Y-m-d H:i");

        $upload = qa_upload_file_one(
            qa_opt('wysiwyg_editor_upload_max_size'),
            $onlyImage, // || !qa_opt('wysiwyg_editor_upload_all'),
            MAX_IMAGE_WIDTH, // max width if it's an image upload
            MAX_IMAGE_WIDTH, // no max height
            $created
        );

        if (isset($upload['error'])) {
            $message = $upload['error'];
        } else {

            // Extract folder from created date in format of Y/M/D, e.g. 2016/01/15
            $dir = substr($created, 0, 4) . '/' . substr($created, 5, 2) . '/' . substr($created, 8, 2);
            $url = "upload/" . $dir . '/' . $upload['blobid'] . '.' . $upload['format'];
            $upload['bloburl'] = $url;
            $in['image'] = $url; 

        }

    }

    // ----------------------------------------

    // TODO Image ???
	qa_get_post_content('editor', 'content', $in['editor'], $in['content'], $in['format'], $in['text']);


	if (!qa_check_form_security_code('ask', qa_post_text('code'))) {
		$errors['page'] = qa_lang_html('misc/form_security_again');
	}
	else {
		$filtermodules = qa_load_modules_with('filter', 'filter_question');
		foreach ($filtermodules as $filtermodule) {
			$oldin = $in;
			$filtermodule->filter_question($in, $errors, null);
			qa_update_post_text($in, $oldin);
		}

		if (qa_using_categories() && count($categories) && (!qa_opt('allow_no_category')) && !isset($in['categoryid'])) {
			// check this here because we need to know count($categories)
			$errors['categoryid'] = qa_lang_html('question/category_required');
		}
		elseif (qa_user_permit_error('permit_post_q', null, $userlevel)) {
			$errors['categoryid'] = qa_lang_html('question/category_ask_not_allowed');
		}

		if ($captchareason) {
			require_once INCLUDE_DIR.'app/captcha.php';
			qa_captcha_validate_post($errors);
		}

		if (empty($errors)) {
			// check if the question is already posted
			$testTitleWords = implode(' ', qa_string_to_words($in['title']));
			$testContentWords = implode(' ', qa_string_to_words($in['content']));
			$recentQuestions = qa_db_select_with_pending(qa_db_qs_selectspec(null, 'created', 0, null, null, false, true, 5));

			foreach ($recentQuestions as $question) {
				if (!$question['hidden']) {
					$qTitleWords = implode(' ', qa_string_to_words($question['title']));
					$qContentWords = implode(' ', qa_string_to_words($question['content']));

					if ($qTitleWords == $testTitleWords && $qContentWords == $testContentWords) {
						$errors['page'] = qa_lang_html('question/duplicate_content');
						break;
					}
				}
			}
		}

		if (empty($errors)) {
			$cookieid = isset($userid) ? qa_cookie_get() : qa_cookie_get_create(); // create a new cookie if necessary

			$questionid = qa_question_create($followanswer, $userid, qa_get_logged_in_handle(), $cookieid,
				$in['title'], $in['content'], $in['format'], $in['text'], isset($in['tags']) ? qa_tags_to_tagstring($in['tags']) : '',
				$in['notify'], $in['email'], $in['categoryid'], $in['extra'], $in['queued'], $in['name'], $in['type'], $in['image'], $in['lead']);

			qa_redirect(qa_q_request($questionid, $in['title'])); // our work is done here
		}
	}
}


// Prepare content for theme

$qa_content = qa_content_prepare(false, array_keys(qa_category_path($categories, @$in['categoryid'])));
$qa_content['title'] = qa_lang_html(isset($followanswer) ? 'question/ask_follow_title' : 'question/ask_title');
$qa_content['error'] = @$errors['page'];

$editorname = isset($in['editor']) ? $in['editor'] : qa_opt('editor_for_qs');
$editor = qa_load_editor(@$in['content'], @$in['format'], $editorname);

$field = qa_editor_load_field($editor, $qa_content, @$in['content'], @$in['format'], 'content', 12, false);
$field['label'] = qa_lang_html('question/q_content_label');
$field['error'] = qa_html(@$errors['content']);

$custom = qa_opt('show_custom_ask') ? trim(qa_opt('custom_ask')) : '';

// NB! Yet another form for EDIT here : qa_page_q_edit_q_form => include/pages/question-post.php

$fields_array = [];

$fields_array['custom'] = [
	'type' => 'custom',
	'note' => $custom,
];

if (qa_get_logged_in_level() == USER_LEVEL_EDITOR || qa_get_logged_in_level() >= USER_LEVEL_ADMIN) {

$fields_array['type'] = [
        "label" => qa_lang_html('question/q_type_label'),
        "error" => "",
        "type" => "select",
        "tags"=> 'name="type" id="type" style="width: 100%;"', 
        "options" => [ 
			"POST" => qa_lang_html('question/the_post'),
			"Q" => qa_lang_html('question/the_question'),
			"LINK" => qa_lang_html('question/the_link'),
		],
        "value" => qa_lang_html('question/the_post'),
    ];

    $fields_array['title'] = [
        'label' => qa_lang_html('question/title_label'),
        'tags' => 'name="title" id="title" autocomplete="off"',
        'value' => qa_html(@$in['title']),
        'error' => qa_html(@$errors['title']),
    ];

} else {

    $fields_array['title'] = [
    	'label' => qa_lang_html('question/q_title_label'),
    	'tags' => 'name="title" id="title" autocomplete="off"',
    	'value' => qa_html(@$in['title']),
    	'error' => qa_html(@$errors['title']),
    ];

}

if (USE_CATEGORIES && count($categories)) {

    $cats = [];
    
    foreach ($categories as $cat) {
        $cats[$cat["categoryid"]] = $cat["title"];
    }

	$fields_array['category'] = [
		'label' => qa_lang_html('question/q_category_label'),
		'error' => qa_html(@$errors['categoryid']),
        "type" => "select",
        "tags"=> 'name="category" id="category" style="width: 100%;"',
        "options" => $cats,
        //"value" => $in['categoryid'],
	];
}

if (qa_get_logged_in_level() == USER_LEVEL_EDITOR || qa_get_logged_in_level() >= USER_LEVEL_ADMIN) {

    $fields_array['image'] = [
        'type' => 'file',
        'label' =>  qa_lang_html('question/image_label'),
		'tags' => 'name="image" style="width: 100%;"',
		'value' => '', 
		'error' => qa_html(@$errors['image']),
	];

// -----------------------------------------------------------------------------

    $fields_array['lead'] = [
        'type' => 'textarea',		
        'label' => qa_lang_html('question/lead_label'), 
		'tags' => 'name="lead" id="type" autocomplete="off" style="height: 75px;"',
		'value' => qa_html(@$in['lead']),
		'error' => qa_html(@$errors['lead']),
	];
}

/* 
$fields_array['similar'] = [
	'type' => 'custom',
	'html' => '<span id="similar"></span>',
];
*/

$fields_array['content'] = $field;

$qa_content['form'] = array(

	'tags' => 'enctype="multipart/form-data" name="ask" method="post" action="'.qa_self_html().'"',

	'style' => 'tall',

	'fields' => $fields_array,

	'buttons' => array(
		'ask' => array(
			'tags' => 'onclick="qa_show_waiting_after(this, false); '.
				(method_exists($editor, 'update_script') ? $editor->update_script('content') : '').'"',
			'label' => qa_lang_html('question/ask_button'),
		),
	),

	'hidden' => array(
		'editor' => qa_html($editorname),
		'code' => qa_get_form_security_code('ask'),
		'doask' => '1',
	),
);

if (!strlen($custom)) {
	unset($qa_content['form']['fields']['custom']);
}

if (qa_opt('do_ask_check_qs') || qa_opt('do_example_tags')) {
	$qa_content['form']['fields']['title']['tags'] .= ' onchange="qa_title_change(this.value);"';

	if (strlen(@$in['title'])) {
		$qa_content['script_onloads'][] = 'qa_title_change('.qa_js($in['title']).');';
	}
}

if (isset($followanswer)) {
	$viewer = qa_load_viewer($followanswer['content'], $followanswer['format']);

	$field = array(
		'type' => 'static',
		'label' => qa_lang_html('question/ask_follow_from_a'),
		'value' => $viewer->get_html($followanswer['content'], $followanswer['format'], array('blockwordspreg' => qa_get_block_words_preg())),
	);

	qa_array_insert($qa_content['form']['fields'], 'title', array('follows' => $field));
}


if (qa_opt('extra_field_active')) {
	$field = array(
		'label' => qa_html(qa_opt('extra_field_prompt')),
		'tags' => 'name="extra"',
		'value' => qa_html(@$in['extra']),
		'error' => qa_html(@$errors['extra']),
	);

	qa_array_insert($qa_content['form']['fields'], null, array('extra' => $field));
}

if (qa_using_tags()) {
	$field = array(
		'error' => qa_html(@$errors['tags']),
	);

	qa_set_up_tag_field($qa_content, $field, 'tags', isset($in['tags']) ? $in['tags'] : array(), array(),
		qa_opt('do_complete_tags') ? array_keys($completetags) : array(), qa_opt('page_size_ask_tags'));

	qa_array_insert($qa_content['form']['fields'], null, array('tags' => $field));
}

if (!isset($userid) && qa_opt('allow_anonymous_naming')) {
	qa_set_up_name_field($qa_content, $qa_content['form']['fields'], @$in['name']);
}

if (ALLOW_EMAIL_NOTIFICATIONS) {
    qa_set_up_notify_fields($qa_content, $qa_content['form']['fields'], 'Q', qa_get_logged_in_email(),
	   isset($in['notify']) ? $in['notify'] : qa_opt('notify_users_default'), @$in['email'], @$errors['email']);
}

if ($captchareason) {
	require_once INCLUDE_DIR.'app/captcha.php';
	qa_set_up_captcha_field($qa_content, $qa_content['form']['fields'], @$errors, qa_captcha_reason_note($captchareason));
}

$qa_content['focusid'] = 'title';


return $qa_content;
