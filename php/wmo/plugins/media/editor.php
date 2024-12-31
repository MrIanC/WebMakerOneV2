<?php

$html_body = str_replace(
    ['#image_file#'],
    ["/{$_GET['edit']}"],
    file_get_contents(__DIR__ . "/editor.html")
);
