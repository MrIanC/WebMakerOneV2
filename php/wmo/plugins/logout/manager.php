<?php
$currentUser = json_decode(base64_decode($_SESSION['authtoken']), true)['username'] ?? "";
$html_body = str_replace(
    ["#username#"],
    [$currentUser],
    file_get_contents(__DIR__ . "/form.html")
);