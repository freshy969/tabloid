<?php

/*
	Description: After renaming, use this to set up database details and other stuff
*/

// TODO Use ENV timezone
date_default_timezone_set('Europe/Moscow');

// TODO Use ENV lang
setlocale(LC_ALL, 'ru_RU');

// Do we use categories for content?
const USE_CATEGORIES = true;

// Other Options
if (!defined('COUNT_VIEWS'))
	define('COUNT_VIEWS', true);

// Allow e-mail notifications when question is answered?
// See Joel Spolsky interview why you should not allow this
const ALLOW_EMAIL_NOTIFICATIONS = false;

// Anti Spam - please use either domain of email with @ prefix (like @spam.com), or the whole email address (like dirty@spammer.com)
const SPAMMERS = [
    '@galaxy.rom11.com', '@buy.exiix.com', '@root.factorican.com', '@baby.360ezzz.com', '@gem.eshreky.com', '@app.britted.com',
    '@proxy.geomenon.com', '@code.variots.com', '@idea.pancingqueen.com', '@dealerlicenses101.com', '@low.toddard.com', '@webhosting.nexgenemails.com',
    '@rollagodno.ru', '@gmx.com', '@see.estaxy.com', '@tree.britted.com', '@app.dobunny.com', '@pro.vocalmajoritynow.com', '@rugbypics.club',
    '@exnik.com', '@a.gsasearchengineranker.pw', '@a.japantravel.network', '@linklist.club', '@d.southafricatravel.club', '@m.articlespinning.club',
    '@c.travel-e-store.com', '@rope.ppoet.com', '@cloud.scoldly.com', '@nerd.vocating.com', '@men.discopied.com', '@final.quirkymeme.com',
    '@n.rugbypics.club', '@clean.scoldly.com', '@ismubarakdead.com', '@platform.clarized.com', '@tab.kellergy.com', '@array.baburn.com', '@tab.kellergy.com',
    '@1mail.x24hr.com', '@desk.discopied.com', '@gem.shamroad.com', '@desktop.summitted.com', '@minor.farthy.com', '@h.bali-traveller.com',
    '@pecinan.com', '@big.citetick.com', '@brunhilde.ml', '@aliyun.com', '@third.boringverse.com', '@tempr.email', '@mailpost.gq',
    '@sort.ruimz.com', '@small.eshreky.com', '@why.estabbi.com', '@e.bali-traveller.com', '@e.linklist.club', '@163.com', '@mess.ppoet.com',
    '@our.ppoet.com', '@tanpablokir.com', '@disposable-email.ml', '@rural.ppoet.com', '@hog.britainst.com', '@pack.memberty.com',
    '@not.radities.com', '@secure.ultramoonbear.com', '@dust.islaby.com', '@street.milltrill.com', '@checkout.pancingqueen.com',
    '@dust.islaby.com', '@legal.clarized.com', '@mailmeservice.club', '@developer.ultramoonbear.com', '@not.radities.com', '@rural.ppoet.com',
    '@pack.memberty.com', '@a.singaporetravel.network', '@checkout.pancingqueen.com', '@pack.memberty.com', '@secure.suspent.com', '@say.jokeray.com',
    '@legal.clarized.com', '@discover.clarized.com', '@g.sportwatch.website', '@discardmail.com',
    '@ruby.baburn.com', '@got.islaby.com', '@space.vocating.com', '@sheep.scoldly.com', '@mars.variots.com', '@moon.iskba.com', '@gem.resistingmoney.com',
    '@edge.wirelax.com', '@docs.intained.com', '@partners.scoldly.com', '@dev.variots.com', '@long.rom11.com', '@edge.wirelax.com',
    '@miss.eastworldwest.com', '@desk.boringverse.com', '@first.memberty.com', '@long.rom11.com', '@for.milltrill.com', '@medium.geomenon.com',
    '@easygk.com', '@extra.baburn.com', '@community.scoldly.com', '@desk.boringverse.com', '@happy.intained.com', '@star.resistingmoney.com',
    '@moon.iskba.com', '@edge.wirelax.com', '@baby.pairst.com', '@smart.baburn.com', '@cleantalkorg5.ru', '@thdv.ru', '@m-dnc.com', '@m-dnc.com',
    '@t.pl', '@cleantalkorg5.ru', '@gxsdkj.com', '@must.eastworldwest.com', '@dev.variots.com'
];

/*
	======================================================================
	  THE 4 DEFINITIONS BELOW ARE REQUIRED AND MUST BE SET BEFORE USING!
	======================================================================
*/

	require_once('../env.php');

	define('MAX_IMAGE_WIDTH', 1080); // Maximum image width = FullHD 1920 or 1080 ?

/*
	======================================================================
	 OPTIONAL CONSTANT DEFINITIONS, INCLUDING SUPPORT FOR SINGLE SIGN-ON
	======================================================================
*/

/*
	If you wish, you can define BLOBS_DIRECTORY to store BLOBs (binary large objects) such
	as avatars and uploaded files on disk, rather than in the database. If so this directory
	must be writable by the web server process - on Unix/Linux use chown/chmod as appropriate.
	Note than if multiple Q2A sites are using MYSQL_USERS_PREFIX to share users, they must
	also have the same value for BLOBS_DIRECTORY.

	If there are already some BLOBs stored in the database from previous uploads, click the
	'Move BLOBs to disk' button in the 'Stats' section of the admin panel to move them to disk.

	define('BLOBS_DIRECTORY', '/path/to/writable_blobs_directory/');
*/

	define('BLOBS_DIRECTORY', dirname(BASE_DIR , 1) . '/public/upload');

/*
	If you wish to use file-based caching, you must define CACHE_DIRECTORY to store the cache
	files. The directory must be writable by the web server. For maximum security it's STRONGLY
	recommended to place the folder outside of the web root (so they can never be accessed via a
	web browser).

	define('CACHE_DIRECTORY', '/path/to/writable_cache_directory/');
*/

	define('CACHE_DIRECTORY', dirname(BASE_DIR , 1) . '/public/cache');

/*
	If you wish, you can define COOKIE_DOMAIN so that any cookies created by Q2A are assigned
	to a specific domain name, instead of the full domain name of the request by default. This is
	useful if you're running multiple Q2A sites on subdomains with a shared user base.

	define('COOKIE_DOMAIN', '.example.com'); // be sure to keep the leading period
*/

/*
	If you wish, you can define an array $CONST_PATH_MAP to modify the URLs used in your Q2A site.
	The key of each array element should be the standard part of the path, e.g. 'questions',
	and the value should be the replacement for that standard part, e.g. 'topics'. If you edit this
	file in UTF-8 encoding you can also use non-ASCII characters in these URLs.
*/

	$CONST_PATH_MAP = [
		'questions'  => 'posts',
		'categories' => 'categories',
		'users'      => 'users',
		'user'       => 'user',
	];

/*
	SetEXTERNAL_USERS to true to use your user identification code in qa-external/qa-external-users.php
	This allows you to integrate with your existing user database and management system. For more details,
	consult the online documentation on installing Tabloid with single sign-on.

	The constantsEXTERNAL_LANG and EXTERNAL_EMAILER are deprecated from Q2A 1.5 since the same
	effect can now be achieved in plugins by using function overrides.
*/

	define('EXTERNAL_USERS', false);

/*
	Out-of-the-box WordPress 3.x integration - to integrate with your WordPress site and user
	database, defineWORDPRESS_INTEGRATE_PATH as the full path to the WordPress directory
	containing wp-load.php. You do not need to set theMYSQL_* constants above since these
	will be taken from WordPress automatically. See online documentation for more details.

	define('WORDPRESS_INTEGRATE_PATH', '/PATH/TO/WORDPRESS');
*/

/*
	Some settings to help optimize your Tabloid site's performance.

	IfHTML_COMPRESSION is true, HTML web pages will be output using Gzip compression, which
	will increase the performance of your site (if the user's browser indicates this is supported).
	This is best done at the server level if possible, but many hosts don't provide server access.

	MAX_LIMIT_START is the maximum start parameter that can be requested, for paging through
	long lists of questions, etc... As the start parameter gets higher, queries tend to get
	slower, since MySQL must examine more information. Very high start numbers are usually only
	requested by search engine robots anyway.

	If a word is usedIGNORED_WORDS_FREQ times or more in a particular way, it is ignored
	when searching or finding related questions. This saves time by ignoring words which are so
	common that they are probably not worth matching on.

	SetALLOW_UNINDEXED_QUERIES to true if you don't mind running some database queries which
	are not indexed efficiently. For example, this will enable browsing unanswered questions per
	category. If your database becomes large, these queries could become costly.

	SetOPTIMIZE_DISTANT_DB to false if your web server and MySQL are running on the same box.
	When viewing a page on your site, this will use many simple MySQL queries instead of fewer
	complex ones, which makes sense since there is no latency for localhost access.
	Otherwise, set it to true if your web server and MySQL are far enough apart to create
	significant latency. This will minimize the number of database queries as much as is possible,
	even at the cost of significant additional processing at each end.

	The optionOPTIMIZE_LOCAL_DB is no longer used, sinceOPTIMIZE_DISTANT_DB covers our uses.

	SetPERSISTENT_CONN_DB to true to use persistent database connections. Requires PHP 5.3.
	Only use this if you are absolutely sure it is a good idea under your setup - generally it is
	not. For more information: http://www.php.net/manual/en/features.persistent-connections.php

	SetDEBUG_PERFORMANCE to true to show detailed performance profiling information at the
	bottom of every Tabloid page.
*/

	define('HTML_COMPRESSION', false);
	define('MAX_LIMIT_START', 1000000); // WAS 19999
	define('IGNORED_WORDS_FREQ', 10000);
	define('ALLOW_UNINDEXED_QUERIES', false);
	define('OPTIMIZE_DISTANT_DB', false);
	define('PERSISTENT_CONN_DB', false);
	define('DEBUG_PERFORMANCE', false);

/*
	And lastly... if you want to, you can predefine any constant from qa-db-maxima.php in this
	file to override the default setting. Just make sure you know what you're doing!
*/
