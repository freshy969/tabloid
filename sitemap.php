<?php

// Sitemap generator

const MAX_RECORDS = 50000;

// Allow ONLY command-line calls
$is_cli = php_sapi_name() === 'cli' OR defined('STDIN');
if (!$is_cli) die();

date_default_timezone_set('Europe/Moscow');

include "env.php";
include "utf8.php"; 

$options = getopt("", ['start::', 'finish::', 'threads::']);
$start = isset($options['start']) ? intval($options['start']) : null;
$finish = isset($options['finish']) ? intval($options['finish']) : $start;
$threads = isset($options['threads']) ? intval($options['threads']) : null;

$db = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
    DB_USER, DB_PASS, [
        PDO::ATTR_TIMEOUT => 10, 
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]
);

if (!$db) die("\n[ERR] Could not connect to DB!");

echo "\n[START] Sitemap generation started!";

$countSQL = $db->prepare('SELECT count(postid) from posts WHERE type = "Q" or type = "POST" ORDER BY postid');
$countSQL->execute();
$count = $countSQL->fetchColumn();
$batches = $count > MAX_RECORDS ? ceil($count / MAX_RECORDS) : 1;
$dir = $batches > 1 ? "./public/sitemaps" : "./public";

// Write single sitemap.xml or partial sitemaps/sitemap.xxxx.xml files with 50K records

for ($i = 0; $i < $batches; $i++) {

    $num = $batches > 1 ? ".$i" : "";
    $file = fopen("$dir/sitemap$num.xml", "w");

    fwrite($file, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
    fwrite($file, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");

    $selectSQL = $db->prepare('SELECT postid, title from posts WHERE type = "Q" or type = "POST" ORDER BY postid LIMIT ' . MAX_RECORDS . ' OFFSET ' . $i * MAX_RECORDS);
    $selectSQL->execute();
    $rows = $selectSQL->fetchAll();

    foreach ($rows as $row) {

        $url = PROTOCOL . '://' . DOMAIN . '/' . $row['postid'] . '/' . slugify($row['title']);
        fwrite($file, "\t<url>\n\t\t<loc>" . xml($url) . "</loc>\n\t</url>\n");

	}

    fwrite($file, "</urlset>\n");

    fclose($file);

}

// If there some partials sitemaps, write main sitemap as reference

if ($batches > 1) {
    $file = fopen("./public/sitemap.xml", "w");
    fwrite($file, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
    fwrite($file, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");
    for ($i = 0; $i < $batches; $i++)
        fwrite($file, "\t<url>\n\t\t<loc>" . PROTOCOL . '://' . DOMAIN . "/sitemaps/sitemap.$i.xml" . "</loc>\n\t</url>\n");
    fwrite($file, "</urlset>\n");
    fclose($file);
}

echo "\n[FINISH] Processed $count [Q] and [POST] records!\n";

function xml($string)
{
	return htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$string));
}
