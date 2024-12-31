<?php
$dir_settings = $settings->settings['out_dir'] . "/wmo/settings";
$filename = "$dir_settings/apikeys.json";

if (($useDB ?? "no") == "yes") {
    $apis = (db_entry_exists($filename, $conn)) ? json_decode(db_get_contents($filename, $conn), true) : [];
} else {
    $apis = (file_exists($filename)) ? json_decode(file_get_contents($filename), true) : [];
}

if (isset($_POST['update'])) {
    $apis['gemini_ai'] = $_POST['gemini_ai'];
    $apis['google_font'] = $_POST['google_font'];

    if (($useDB ?? "no") == "yes") {
        db_put_contents($filename, json_encode($apis, JSON_PRETTY_PRINT), $conn);
    } else {
        file_put_contents($filename, json_encode($apis, JSON_PRETTY_PRINT));
    }
}

$html_body = str_replace(
    [
        '#gemini_ai#',
        '#google_font#'
    ],
    [
        $apis['gemini_ai'] ?? "",
        $apis['google_font'] ?? "",
    ],
    file_get_contents(__DIR__ . "/form.html")
);