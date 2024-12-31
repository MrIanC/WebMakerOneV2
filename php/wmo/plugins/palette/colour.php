<?php

$palette_template = '
<div class="pb-1  my-1 d-flex align-items-center justify-content-between border-bottom">
    <div class="d-flex align-items-center">
        <div style="background-color: #customcolour#; height:32px; width: 32px;"></div>
        <div class="ms-2">#color_name#</div>
    </div>
    <div>
        <button class="btn btn-primary btn-sm" name="delete" value="#color_name#"><i class="bi bi-x-square"></i></button>
    </div>

</div>
';
$palette_examples = '
<div class="col-12 col-sm-6 col-md-4">
    <div class="border rounded shadow mb-2 p-2">
        <div class="text-custom-#color_name# mb-1 p-1">text-custom-#color_name#</div>
        <div class="bg-custom-#color_name# mb-1 p-1">bg-custom-#color_name#</div>
        <div class="btn-custom-#color_name# mb-1 btn btn-sm">btn-custom-#color_name#</div>
    </div>
</div>

';

$dir_settings = $settings->settings['out_dir'] . "/wmo/settings";
$dir_content = $settings->settings['out_dir'] . "/wmo/content";
$filename = "$dir_settings/palette.json";

if (($useDB ?? "no") == "yes") {
    $palette = (db_entry_exists($filename, $conn)) ? json_decode(db_get_contents($filename, $conn), true) : [];
} else {
    $palette = (file_exists($filename)) ? json_decode(file_get_contents($filename), true) : [];
}
/*
$palette = (file_exists($filename)) ? json_decode(file_get_contents($filename), true) : [];
db_put_contents($filename, json_encode($palette, JSON_PRETTY_PRINT), $conn);
*/
if (isset($_POST['Add'], $_POST['customcolour'], $_POST['color_name'])) {
    $palette[$_POST['color_name']] = $_POST['customcolour'];

    if (($useDB ?? "no") == "yes") {
        db_put_contents($filename, json_encode($palette, JSON_PRETTY_PRINT), $conn);
    } else {
        file_put_contents($filename, json_encode($palette, JSON_PRETTY_PRINT));
    }

    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");
}
if (isset($_POST['delete'])) {
    unset($palette[$_POST['delete']]);

    if (($useDB ?? "no") == "yes") {
        db_put_contents($filename, json_encode($palette, JSON_PRETTY_PRINT), $conn);
    } else {
        file_put_contents($filename, json_encode($palette, JSON_PRETTY_PRINT));
    }

    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");
}

$colour_list = "";
$colour_examples = "";

foreach ($palette as $key => $value) {
    $colour_list .= str_replace(
        ['#color_name#', '#customcolour#'],
        [$key, $value],
        $palette_template
    );
    $colour_examples .= str_replace(
        ['#color_name#', '#customcolour#'],
        [$key, $value],
        $palette_examples
    );

    $red = hexdec(substr($value, 1, 2));
    $gre = hexdec(substr($value, 3, 2));
    $blu = hexdec(substr($value, 5, 2));

    $rgbvalue = "$red, $gre, $blu";

    $cssParts[':root'][] = "--gen-$key:$value;";
    $cssParts[':root'][] = "--gen-$key-rgb:$rgbvalue;";

    $cssParts[".text-custom-$key"][] = "--bs-text-opacity: 1;";
    $cssParts[".text-custom-$key"][] = "color: rgba($rgbvalue, var(--bs-text-opacity)) !important;";

    $cssParts[".bg-custom-$key"][] = "--bs-bg-opacity: 1;";
    $cssParts[".bg-custom-$key"][] = "background-color: rgba($rgbvalue, var(--bs-bg-opacity)) !important;";

    $luminance = (0.2126 * ($red / 255)) +
        (0.7152 * ($gre / 255)) +
        (0.0722 * ($blu / 255));

    $bwcolor = ($luminance > 0.5) ? "#000000" : "#ffffff";

    $factor = ($luminance > 0.5) ? 0.8 : 1.2;

    $newR = min(255, round($red * $factor));
    $newG = min(255, round($gre * $factor));
    $newB = min(255, round($blu * $factor));

    $newColor = "#" . dechex($newR) . dechex($newG) . dechex($newB);

    $cssParts[".btn-custom-$key"][] = "--bs-btn-color: $bwcolor;";
    $cssParts[".btn-custom-$key"][] = "--bs-btn-bg: $value;";
    $cssParts[".btn-custom-$key"][] = "--bs-btn-border-color: $value;";
    $cssParts[".btn-custom-$key"][] = "--bs-btn-hover-color: $bwcolor;";
    $cssParts[".btn-custom-$key"][] = "--bs-btn-hover-bg: $newColor;";
    $cssParts[".btn-custom-$key"][] = "--bs-btn-hover-border-color: $newColor;";
    $cssParts[".btn-custom-$key"][] = "--bs-btn-focus-shadow-rgb: 49, 132, 253;";
    $cssParts[".btn-custom-$key"][] = "--bs-btn-active-color: $bwcolor;";
    $cssParts[".btn-custom-$key"][] = "--bs-btn-active-bg: $newColor;";
    $cssParts[".btn-custom-$key"][] = "--bs-btn-active-border-color: $newColor;";
    $cssParts[".btn-custom-$key"][] = "--bs-btn-active-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);";
    $cssParts[".btn-custom-$key"][] = "--bs-btn-disabled-color: #aaa;";
    $cssParts[".btn-custom-$key"][] = "--bs-btn-disabled-bg: #ffffff;";
    $cssParts[".btn-custom-$key"][] = "--bs-btn-disabled-border-color: #ffffff;";
}



$cssParts[".bg-opacity-10"][] = "--bs-bg-opacity: 0.1;";
$cssParts[".bg-opacity-25"][] = "--bs-bg-opacity: 0.25;";
$cssParts[".bg-opacity-50"][] = "--bs-bg-opacity: 0.5;";
$cssParts[".bg-opacity-75"][] = "--bs-bg-opacity: 0.75;";
$cssParts[".bg-opacity-100"][] = "--bs-bg-opacity: 1;";

$cssFile = [];
foreach ($cssParts as $key => $value) {
    $cssFile[] = "$key {" . implode($value) . "}";
}

$cssFilename = "$dir_content/css/palette.css";
if (($useDB ?? "no") == "yes") {
    db_put_contents($cssFilename, implode($cssFile), $conn);
} else {
    file_put_contents($cssFilename, implode($cssFile));
}




$html_body = str_replace(
    ['#color-list#', '#color-examples#'],
    [$colour_list, $colour_examples],
    file_get_contents(__DIR__ . "/form.html")
);

$html_body .= "<style>" . implode($cssFile) . "</style>";