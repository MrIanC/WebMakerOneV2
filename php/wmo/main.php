<?php

$wmo_menu = [];
$html_body = "<div></div>";
$wmo_menu_html = "<div></div>";


$wmo_plugins = json_decode(file_get_contents(__DIR__ . "/plugins/plugin.json"), true);
foreach (glob(__DIR__ . "/plugins/*/config.json") as $file) {
    $key = basename(dirname($file));
    $wmo_plugins[$key] = $file;
}

foreach ($wmo_plugins as $key => $file) {
    if ($wmo_plugins[$key] == "order") {
        unset($wmo_plugins[$key]);
    }
}

foreach ($wmo_plugins as $key => $file) {
    $decode = json_decode(file_get_contents($file), true);
    if (isset($decode['menu'])) {
        $wmo_menu[] = $decode['menu'];
    }
    if (isset($decode['include'])) {
        include dirname($file) . "/" . $decode['include'] . ".php";
    }
}

if (isset($_GET['wmo'])) {
    $file = $wmo_plugins[$_GET['wmo']];
    $decode = json_decode(file_get_contents($file), true);
    if (isset($decode['page'])) {
        include dirname($file) . "/" . $decode['page'] . ".php";
    }
} else {
    $help_sections = "";
    foreach (glob(__DIR__ . "/plugins/*/help.html") as $file) {
        $help_sections .= file_get_contents($file);
    }

    $html_body = str_replace(
        ["#help#"],
        [$help_sections],
        file_get_contents(__DIR__ . "/main.html")
    );
}

$percentComplete = check_completion();


$all = str_replace(
    ["#menu#", "#content#", "#percentComplete#"],
    [$wmo_menu_html, $html_body, $percentComplete],
    file_get_contents(__DIR__ . "/layout.html")
);
$htmlDOC->inject("body", $all, "start");
