<?php

/*
	Definitions relating to favorites and updates in the database tables
*/

// Character codes for the different types of entity that can be followed (entitytype columns)

define('ENTITY_QUESTION', 'Q');
define('ENTITY_USER',     'U');
define('ENTITY_TAG',      'T');
define('ENTITY_CATEGORY', 'C');
define('ENTITY_NONE',     '-');

// Tabloid

define('ENTITY_POST',     'P');
define('ENTITY_LINK',     'L');

// Character codes for the different types of updates on a post (updatetype columns)

define('UPDATE_CATEGORY', 'A'); // questions only, category changed
define('UPDATE_CLOSED',   'C'); // questions only, closed or reopened
define('UPDATE_CONTENT',  'E'); // title or content edited
define('UPDATE_PARENT',   'M'); // e.g. comment moved when converting its parent answer to a comment
define('UPDATE_SELECTED', 'S'); // answers only, removed if unselected
define('UPDATE_TAGS',     'T'); // questions only
define('UPDATE_TYPE',     'Y'); // e.g. answer to comment
define('UPDATE_VISIBLE',  'H'); // hidden or reshown

// Character codes for types of update that only appear in the streams tables, not on the posts themselves

define('UPDATE_FOLLOWS',  'F'); // if a new question was asked related to one of its answers, or for a comment that follows another
define('UPDATE_C_FOR_Q',  'U'); // if comment created was on a question of the user whose stream this appears in
define('UPDATE_C_FOR_A',  'N'); // if comment created was on an answer of the user whose stream this appears in
