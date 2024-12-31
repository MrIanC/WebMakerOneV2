<?php
$pages_list = "";
$restore_list = "";
$pagelistTemplate = '
<div class="d-flex justify-content-between">
    <div>
        <i class="bi #landingpage#"></i>
        <a href="/app/php/wmo/plugins/editor/editor.php?page=pages/#pagename#">
            <span>#pagename#</span>
        </a>
    </div>
    
    <div>
        <button class="btn btn-primary btn-sm mb-1" name="home" value="#pagename#"><i class="bi bi-house"></i></button>
        <button class="btn btn-primary btn-sm mb-1" name="delete" value="#pagename#"><i class="bi bi-file-earmark-x"></i></button>
    </div>
</div>
';
$restorelistTemplate = '
<div class="d-flex justify-content-between">
<a href="/app/php/wmo/plugins/editor/editor.php?page=pages/#pagename#">
#pagename#
</a>
<div class="">
    <button class="btn btn-primary btn-sm mb-1" name="restore" value="#pagename#"><i class="bi bi-life-preserver"></i></button>
    <button class="btn btn-danger btn-sm mb-1" name="permadelete" value="#pagename#"><i class="bi bi-file-earmark-x"></i></button>
</div>
</div>
';

$dir_content_pages = $settings->settings['out_dir'] . "/wmo/content/pages";
$dir_settings = $settings->settings['out_dir'] . "/wmo/settings";
$landingfilename = $dir_settings . "/landing.json";

if (isset($_POST['new_page_name'])) {
    $filename = "$dir_content_pages/{$_POST['new_page_name']}.html";

    if (($useDB ?? "no") == "yes") {
        if (!db_entry_exists($filename, $conn)) {
            db_put_contents($filename, "", $conn);
        }
    } else {
        if (!file_exists($filename)) {
            file_put_contents($filename, "");
        }
    }

    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");
}



if (isset($_POST['delete'])) {
    $filename = "$dir_content_pages/{$_POST['delete']}";

    if (($useDB ?? "no") == "yes") {
        $tmp = db_get_contents($filename, $conn);
        db_put_contents($filename . date("YmdHi") . ".bak", $tmp, $conn);
        db_unlink($filename, $conn);
    } else {
        copy($filename, $filename . date("YmdHi") . ".bak");
        unlink($filename);
    }

    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");

}
if (isset($_POST['permadelete'])) {
    $filename = "$dir_content_pages/{$_POST['permadelete']}";
    if (($useDB ?? "no") == "yes") {
        db_unlink($filename, $conn);
    } else {
        unlink($filename);
    }
    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");
}

if (isset($_POST['restore'])) {
    $filename = "$dir_content_pages/{$_POST['restore']}";
    if (($useDB ?? "no") == "yes") {
        //echo $filename;
        $end = strpos($filename, ".html");
        $restorefilename = substr($filename, 0, $end) . ".html";
        $tmp = db_get_contents($filename, $conn);
        db_unlink($filename, $conn);
        db_put_contents($restorefilename, $tmp, $conn);
    } else {
        $end = strpos($filename, ".html");
        $restorefilename = substr($filename, 0, $end) . ".html";
        if (!file_exists(str_replace(".bak", "", $filename))) {
            copy($filename, $restorefilename);
            unlink($filename);
        }
    }
    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");

}

if (isset($_POST['home'])) {
    $landing = [
        "landing" => $_POST['home']
    ];

    if (($useDB ?? "no") == "yes") {
        db_put_contents($landingfilename, json_encode($landing, JSON_PRETTY_PRINT), $conn);
    } else {
        file_put_contents($landingfilename, json_encode($landing, JSON_PRETTY_PRINT));
    }

    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");
}

if (($useDB ?? "no") == "yes") {
    $landjson = (db_entry_exists($landingfilename, $conn)) ? json_decode(db_get_contents($landingfilename, $conn), true) : [];
} else {
    $landjson = file_exists($landingfilename) ? json_decode(file_get_contents($landingfilename), true) : [];
}
$landingpagename = $landjson['landing'] ?? "";

if (($useDB ?? "no") == "yes") {
    foreach (db_glob("$dir_content_pages/*.html", $conn) as $files) {
        $page_name = basename($files);
        $icon = ($landingpagename == $page_name) ? 'bi-file-earmark-fill' : 'bi-file';
        $pages_list .= str_replace(['#pagename#', '#landingpage#'], [$page_name, $icon], $pagelistTemplate);
    }
} else {
    foreach (glob("$dir_content_pages/*.html") as $files) {
        $page_name = basename($files);
        $icon = ($landingpagename == $page_name) ? 'bi-file-earmark-fill' : 'bi-file';
        $pages_list .= str_replace(['#pagename#', '#landingpage#'], [$page_name, $icon], $pagelistTemplate);
    }
}

$landing_exists = "Landing Page is set";

if ($landingpagename == '')
    $landing_exists = "Please set a landing page";
if (empty($landjson))
    $landing_exists = "Please set a landing page";

if (!file_exists("$dir_content_pages/$landingpagename")) {
    $landing_exists = "Please set a landing page";
}
if (($useDB ?? "no") == "yes") {
    foreach (db_glob("$dir_content_pages/*.bak", $conn) as $files) {
        $page_name = basename($files);
        $restore_list .= str_replace(['#pagename#'], [$page_name], $restorelistTemplate);
    }
} else {
    foreach (glob("$dir_content_pages/*.bak") as $files) {
        $page_name = basename($files);
        $restore_list .= str_replace(['#pagename#'], [$page_name], $restorelistTemplate);
    }
}


$html_body = str_replace(
    ['#page-list#', '#restore-list#', '#landing-exists#'],
    [$pages_list, $restore_list, $landing_exists],
    file_get_contents(__DIR__ . "/form.html")
);