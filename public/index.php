<?php

/*
	A stub that only sets up the Tabloid root and includes qa-index.php
*/

// Set base path here so this works with symbolic links for multiple installations

define('BASE_DIR', dirname(__FILE__ , 2) . '/tabloid/');
define('PUBLIC_DIR', dirname(__FILE__) . '/');

require BASE_DIR . 'include/index.php';
