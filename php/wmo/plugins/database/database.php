<?php

$count_files = count(db_glob("*", $conn));
$html_body = str_replace(
    [
        '#database_name#',
        '#database_username#',
        '#database_password#',
        '#database_server#',
        "#count_files#"
    ],
    [
        $dbname,
        $username,
        $password,
        $servername,
        $count_files
    ],
    file_get_contents(__DIR__ . "/form.html")
);