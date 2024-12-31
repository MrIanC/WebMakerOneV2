<?php
$nav_template = '
<div class="d-flex justify-content-between align-items-center border-bottom">
    <div>
        <div class="d-flex justify-content-between align-items-center">
            <i class="fs-3 p-2 bi #landing#"></i>
            <div>
                <div>
                    #navtext#
                </div>
                <div class="text-secondary small">
                    #navurl#
                </div>
            </div>
        </div>
    </div>
    <div>
        <button class="btn btn-primary btn-sm" name="delete" value="#navtext#"><i class="bi bi-x-square"></i></button>
        <button class="btn btn-primary btn-sm" name="move" value="#navtext#"><i class="bi bi-arrow-bar-up"></i></button>
    </div>
</div>
';



$datalist_options = "";
$navigation_text = "";
$dir_settings = $settings->settings['out_dir'] . "/wmo/settings";
$dir_content = $settings->settings['out_dir'] . "/wmo/content";
if (!is_dir($dir_settings)) {
    mkdir($dir_settings);
}

$filename = "$dir_settings/navigation.json";

if (($useDB ?? "no") == "yes") {
    $navigation = (db_entry_exists($filename, $conn)) ? json_decode(db_get_contents($filename, $conn), true) : [];
} else {
    $navigation = (file_exists($filename)) ? json_decode(file_get_contents($filename), true) : [];
}


if (isset($_POST['navigation_text'])) {
    $navigation[] = [
        'navtext' => $_POST['navigation_text'],
        'navurl' => (str_contains("http", $_POST['navigation_url']) ? $_POST['navigation_url'] : "/" . str_replace(".html", "", $_POST['navigation_url'])) ?? '/',
    ];

    if (($useDB ?? "no") == "yes") {
        db_put_contents($filename, json_encode($navigation, JSON_PRETTY_PRINT), $conn);
    } else {
        file_put_contents($filename, json_encode($navigation, JSON_PRETTY_PRINT));
    }

    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");

}

if (isset($_POST['delete'])) {
    foreach ($navigation as $key => $value) {
        echo $value['navtext'];
        if ($value['navtext'] == $_POST['delete']) {
            unset($navigation[$key]);
        }
    }
    if (($useDB ?? "no") == "yes") {
        db_put_contents($filename, json_encode($navigation, JSON_PRETTY_PRINT), $conn);
    } else {
        file_put_contents($filename, json_encode($navigation, JSON_PRETTY_PRINT));
    }
    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");

}

if (isset($_POST['move'])) {
    foreach ($navigation as $key => $value) {
        echo $value['navtext'];
        if ($value['navtext'] == $_POST['move']) {
            $move = $key - 1;
            if ($move >= 0) {
                $tmp = $navigation[$move];
                $navigation[$move] = $navigation[$key];
                $navigation[$key] = $tmp;
            }
        }
    }
    $navigation = array_values($navigation);
    
    if (($useDB ?? "no") == "yes") {
        db_put_contents($filename, json_encode($navigation, JSON_PRETTY_PRINT), $conn);
    } else {
        file_put_contents($filename, json_encode($navigation, JSON_PRETTY_PRINT));
    }

    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");
}

if (($useDB ?? "no") == "yes") {
    foreach(db_glob("$dir_content/pages/*.html",$conn) as $files) {
        $page_name = basename($files);
        $datalist_options .= '<option value="' . $page_name . '"></option>';
    }
} else {
    foreach (glob("$dir_content/pages/*.html") as $files) {
        $page_name = basename($files);
        $datalist_options .= '<option value="' . $page_name . '"></option>';
    }

}


foreach ($navigation as $key => $value) {
    $navigation_text .= str_replace(
        ['#navtext#', '#navurl#', '#landing#'],
        [$value['navtext'], $value['navurl'], (($value['landing'] ?? "no") == "yes") ? 'bi-file-earmark-check' : 'bi-file-earmark'],
        $nav_template
    );
}


$filename = dirname($settings->settings['plugins']['wmo']) . "/content/footer/footer.html";
$navigation_exists = "Navigation file does not exist";

if (($useDB ?? "no") == "yes") {
    if (db_entry_exists($filename, $conn)) {
        $navigation_exists = "Navigation file exists";
    }
} else {
    if (file_exists($filename)) {
        $navigation_exists = "Navigation file exists";
    }

}




$html_body = str_replace(
    ['#datalist-options#', '#navigation-used#', '#navigation-exists#'],
    [$datalist_options, $navigation_text, $navigation_exists],
    file_get_contents(__DIR__ . "/form.html")
);