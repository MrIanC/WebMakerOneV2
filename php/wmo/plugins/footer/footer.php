<?php

$dir_content = $settings->settings['out_dir'] . "/wmo/content";
$footer_file_name = "$dir_content/footer/footer.html";
$footer_exists = "Footer file does not exist";

if (($useDB ?? "no") == "yes") {
    if (db_entry_exists($footer_file_name, $conn)) {
        $footer_exists = "Footer file exists";
    }
} else {
    if (file_exists($navigation_file_name)) {
        $footer_exists = "Footer file exists";
    }

}

$html_body = str_replace(
    ['#footer-exists#'],
    [$footer_exists],
    file_get_contents(__DIR__ . "/form.html")
);