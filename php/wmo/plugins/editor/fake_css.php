<?php
$cssFile = dirname($_SERVER['DOCUMENT_ROOT']) . "/wmo/content/css/{$_GET['filename']}";
if (!file_exists($cssFile)) {
    http_response_code(404);
    exit("CSS file not found");
}

header('Content-Type: text/css');
header('Content-Length: ' . filesize($cssFile));
readfile($cssFile);
exit;