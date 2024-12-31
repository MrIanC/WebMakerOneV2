<?php
/**
 * Font Management Script
 * 
 * This script allows users to manage Google Fonts in a web application. It includes functionalities to:
 * - Retrieve and cache font data from the Google Fonts API
 * - Select fonts for headings, paragraphs, and navigation
 * - Generate dynamic font previews
 * - Save selected fonts to a database or file
 */

$dir_settings = $settings->settings['out_dir'] . "/wmo/settings";
$dir_content = $settings->settings['out_dir'] . "/wmo/content";
$fontsettings = "$dir_settings/font.json";
$filename = "$dir_settings/apikeys.json";
$search_term = $_GET['search'] ?? "";

if (($useDB ?? "no") == "yes") {
    $font_use = (db_entry_exists($fontsettings, $conn)) ? json_decode(db_get_contents($fontsettings, $conn), true) : [];
    $apis = (db_entry_exists($filename, $conn)) ? json_decode(db_get_contents($filename, $conn), true) : [];
} else {
    $font_use = (file_exists($fontsettings)) ? json_decode(file_get_contents($fontsettings), true) : [];
    $apis = (file_exists($filename)) ? json_decode(file_get_contents($filename), true) : [];
}

$font_api = $apis['google_font'] ?? "";
$font_url = "https://www.googleapis.com/webfonts/v1/webfonts?key=$font_api";


if (isset($_POST['google_font_api_key'])) {

    $font_api = $_POST['google_font_api_key'];
    $font_url = "https://www.googleapis.com/webfonts/v1/webfonts?key=$font_api";

    $response = file_get_contents($font_url);
    $apis['google_font'] = ($response === FALSE) ? "" : $_POST['google_font_api_key'];

    if (($useDB ?? "no") == "yes") {
        db_put_contents($filename, json_encode($apis, JSON_PRETTY_PRINT), $conn);
    } else {
        file_put_contents($filename, json_encode($apis, JSON_PRETTY_PRINT));
    }



    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");
}

if (isset($_POST['font_name'], $_POST['useFont'])) {
    $font_use[$_POST['useFont']] = $_POST['font_name'];


    if (($useDB ?? "no") == "yes") {
        db_put_contents($fontsettings, json_encode($font_use, JSON_PRETTY_PRINT), $conn);
    } else {
        file_put_contents($fontsettings, json_encode($font_use, JSON_PRETTY_PRINT));
    }


    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");
}

/* Font Data Retrieval and Caching */
$fontcache = "$dir_settings/google_font.json";
if (($useDB ?? "no") == "yes") {
    if (db_entry_exists($fontcache, $conn)) {
        echo "gf_exists";
        $response = db_get_contents($fontcache, $conn);
    } else {
        $response = file_get_contents($font_url);
        db_put_contents($fontcache, $response, $conn);
    }
} else {
    if (file_exists($fontcache)) {
        $response = file_get_contents($fontcache);
    } else {
        $response = file_get_contents($font_url);
        file_put_contents($fontcache, $response);
    }
}



$startIndex = $_GET['startindex'] ?? 0;
$itemsPerPage = 10;

if ($response === FALSE) {
    $font_html[] = "Something went wrong";
} else {
    $fonts = json_decode($response, true);
    $fontsearch = [];
    foreach ($fonts['items'] as $kk => $font) {
        //print_r($font['family']);
        if (str_starts_with(strtolower($font['family']), strtolower($search_term))) {
            $fontsearch['items'][] = $font;
        }
    }
    //print_r($fonts);
    if (!empty($fontsearch['items'])) {
        $fontsToShow = array_slice($fontsearch['items'], $startIndex, $itemsPerPage);
    } else {
        $fontsToShow = array_slice($fonts['items'], $startIndex, $itemsPerPage);
    }

    foreach ($fontsToShow as $font) {
        $font_html[] = str_replace(
            "#font-family#",
            $font['family'],
            '
        <link href="https://fonts.googleapis.com/css2?family=#font-family#" rel="stylesheet" />
            <div class="d-flex justify-content-between align-items-center border-bottom">
                <div style="font-family: #font-family#;">#font-family#</div>
                <form class="d-flex" method="post">
                    <div class="p-2">
                        <input type="hidden" name="font_name" value="#font-family#" />
                        <button class="btn btn-primary btn-sm mb-1" name="useFont" value="headings"><i class="bi bi-type-h1"></i></button>
                        <button class="btn btn-primary btn-sm mb-1" name="useFont" value="paragraph"><i class="bi bi-paragraph"></i></button>
                        <button class="btn btn-primary btn-sm mb-1" name="useFont" value="nav"><i class="bi bi-list"></i></button>
                    </div>
                </form>
            </div>'
        );
    }
}

$pagination = '<a href="?wmo=fonts&amp;search=' . $search_term . '&amp;startindex=' . ((($startIndex - $itemsPerPage) >= 0) ? ($startIndex - $itemsPerPage) : 0) . '" class="btn btn-primary btn-sm mx-1">Prev</a>';
$pagination .= '<a href="?wmo=fonts&amp;search=' . $search_term . '&amp;startindex=' . ($startIndex + $itemsPerPage) . '" class="btn btn-primary btn-sm mx-1">Next</a>';


$font_use['headings'] ??= "";
$font_use['paragraph'] ??= "";
$font_use['nav'] ??= "";


if (!isset($font_html)) {
    $font_html[] = "No font Found";
}

//
$font_heading = '<div>
    <link href="https://fonts.googleapis.com/css2?family=' . $font_use['headings'] . '" rel="stylesheet" />
    <div class="pb-3" style="font-family: ' . $font_use['headings'] . ';">' . $font_use['headings'] . '</div>
    <div class="h1" style="font-family: ' . $font_use['headings'] . ';">' . $font_use['headings'] . '</div>
    <div class="h2" style="font-family: ' . $font_use['headings'] . ';">' . $font_use['headings'] . '</div>
    <div class="h3" style="font-family: ' . $font_use['headings'] . ';">' . $font_use['headings'] . '</div>
</div>';
$font_paragraph = '<div>
    <link href="https://fonts.googleapis.com/css2?family=' . $font_use['paragraph'] . '" rel="stylesheet" />
    <div class="pb-3" style="font-family: ' . $font_use['paragraph'] . ';">' . $font_use['paragraph'] . '</div>
    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. </p>

</div>';
$font_navigation = '<div>
    <link href="https://fonts.googleapis.com/css2?family=' . $font_use['nav'] . '" rel="stylesheet" />
      <div class="pb-3" style="font-family: ' . $font_use['nav'] . ';">' . $font_use['nav'] . '</div>
      <div class="btn btn-link" style="font-family: ' . $font_use['nav'] . ';">Home</div>
      <div class="btn btn-dark" style="font-family: ' . $font_use['nav'] . ';">Home</div>
      <div class="btn btn-primary" style="font-family: ' . $font_use['nav'] . ';">Home</div>
      <div class="btn btn-secondary" style="font-family: ' . $font_use['nav'] . ';">Home</div>
</div>';

$css_file = $dir_content . "/css/fonts.css";


$fonts = json_decode($response, true);
$font_css_url = [];
foreach ($fonts['items'] as $key => $value) {
    if ($font_use['headings'] == $value['family']) {
        $font_css_url['headings'] = $value['files']['regular'] ?? $value['files'][array_key_first($value['files'])];
    }
    if ($font_use['paragraph'] == $value['family']) {
        $font_css_url['paragraph'] = $value['files']['regular'] ?? $value['files'][array_key_first($value['files'])];
    }
    if ($font_use['nav'] == $value['family']) {
        $font_css_url['nav'] = $value['files']['regular'] ?? $value['files'][array_key_first($value['files'])];
    }
}

$cssFileContents = str_replace(
    ['#heading-font#', '#paragraph-font#', '#navigation-font#'],
    [$font_css_url['headings'] ?? "", $font_css_url['paragraph'] ?? "", $font_css_url['nav'] ?? ""],
    file_get_contents(__DIR__ . "/fonts.css")
);

if (($useDB ?? "no") == "yes") {
    db_put_contents($css_file, $cssFileContents, $conn);
} else {
    file_put_contents($css_file, $cssFileContents);
}




if ($font_api == "") {
    $html_body = file_get_contents(__DIR__ . "/api.html");
} else {

    $html_body = str_replace(
        ['#google_font_api_saved_key#', '#font-list#', '#pagination#', '#font-heading#', '#font-paragraph#', '#font-navigation#', '#search#'],
        [$font_api, implode($font_html), $pagination, $font_heading, $font_paragraph, $font_navigation, $search_term],
        file_get_contents(__DIR__ . "/form.html")
    );
}