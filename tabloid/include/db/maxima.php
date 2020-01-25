<?php

/*
	Definitions that determine database column size and rows retrieved
*/


$maximaDefaults = array(
	// Maximum column sizes - any of these can be defined in qa-config.php to override the defaults below,
	// but you need to do so before creating the database, otherwise it's too late.
	'DB_MAX_EMAIL_LENGTH' => 100,
	'DB_MAX_HANDLE_LENGTH' => 50,
	'DB_MAX_TITLE_LENGTH' => 1000,
	'DB_MAX_CONTENT_LENGTH' => 1000000, 
	'DB_MAX_FORMAT_LENGTH' => 20,
	'DB_MAX_TAGS_LENGTH' => 1000,
	'DB_MAX_NAME_LENGTH' => 50,
	'DB_MAX_WORD_LENGTH' => 50,
	'DB_MAX_CAT_PAGE_TITLE_LENGTH' => 100,
	'DB_MAX_CAT_PAGE_TAGS_LENGTH' => 200,
	'DB_MAX_CAT_CONTENT_LENGTH' => 1000,
	'DB_MAX_WIDGET_TAGS_LENGTH' => 1000,
	'DB_MAX_WIDGET_TITLE_LENGTH' => 100,
	'DB_MAX_OPTION_TITLE_LENGTH' => 50,
	'DB_MAX_PROFILE_TITLE_LENGTH' => 50,
	'DB_MAX_PROFILE_CONTENT_LENGTH' => 100000,
	'DB_MAX_CACHE_AGE' => 86400,
	'DB_MAX_BLOB_FILE_NAME_LENGTH' => 255,
	'DB_MAX_META_TITLE_LENGTH' => 50,
	'DB_MAX_META_CONTENT_LENGTH' => 10000,

	// How many records to retrieve for different circumstances. In many cases we retrieve more records than we
	// end up needing to display once we know the value of an option. Wasteful, but allows one query per page.
	'DB_RETRIEVE_QS_AS' => 50,
	'DB_RETRIEVE_TAGS' => 200,
	'DB_RETRIEVE_USERS' => 200,
	'DB_RETRIEVE_ASK_TAG_QS' => 500,
	'DB_RETRIEVE_COMPLETE_TAGS' => 1000,
	'DB_RETRIEVE_MESSAGES' => 20,

	// Keep event streams trimmed - not worth storing too many events per question because we only display the
	// most recent event for each question, that has not been invalidated due to hiding/unselection/etc...
	'DB_MAX_EVENTS_PER_Q' => 5,
);

foreach ($maximaDefaults as $key => $def) {
	if (!defined($key)) {
		define($key, $def);
	}
}
