<?php
include $_SERVER['DOCUMENT_ROOT'] . "/app/php/database/database.php";

$cssFile = dirname($_SERVER['DOCUMENT_ROOT']) . "/wmo/content/css/{$_GET['filename']}";

$tmp = db_get_contents($cssFile,$conn);

if (!db_entry_exists($cssFile,$conn)) {
    http_response_code(404);
    exit("CSS file not found");
}

header('Content-Type: text/css');
header('Content-Length: ' . filesize($cssFile));
echo $tmp;
echo "/* This is from a DB */";
exit;