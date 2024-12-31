<?php
$used_template = '
<div class="row align-items-center border-bottom">
    <div class="col-9">    
        <div>
            #includes_name#    
        </div>
        <div class="small text-secondary row">
            <div class="text-truncate">
                #includes_url#
            </div>
            <div class="small col-12">
            #includes_location# - #includes_type#
            </div>
        </div>
    </div>
    <div class="col-3 text-end">
        <button class="btn btn-primary btn-sm my-1" name="delete" value="#includes_name#" #disabled#><i class="bi bi-x-square"></i></button>
        <button class="btn btn-primary btn-sm my-1" name="move" value="#includes_name#" #disabled#><i class="bi bi-arrow-bar-up"></i></button>
    </div>
</div>
';
$defaultjson = '{
    "2": {
        "includes_name": "UTF-8",
        "includes_url": "    <meta charset=\"UTF-8\" \/>\r\n",
        "includes_location": "Head - Start",
        "includes_type": "HTML"
    },
    "3": {
        "includes_name": "ViewPort",
        "includes_url": "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\" \/>",
        "includes_location": "Head - Start",
        "includes_type": "HTML"
    },
    "4": {
        "includes_name": "JQuery",
        "includes_url": "https:\/\/cdnjs.cloudflare.com\/ajax\/libs\/jquery\/3.7.1\/jquery.min.js",
        "includes_location": "Head - Start",
        "includes_type": "Script"
    },
    "7": {
        "includes_name": "Bootstrap.icons",
        "includes_url": "https:\/\/cdnjs.cloudflare.com\/ajax\/libs\/bootstrap-icons\/1.11.3\/font\/bootstrap-icons.min.css",
        "includes_location": "Head - Start",
        "includes_type": "Stylesheet"
    },
    "8": {
        "includes_name": "bootstrap.js",
        "includes_url": "https:\/\/cdnjs.cloudflare.com\/ajax\/libs\/bootstrap\/5.3.3\/js\/bootstrap.min.js",
        "includes_location": "Body - End",
        "includes_type": "Script"
    },
    "5": {
        "includes_name": "bootstrap.css",
        "includes_url": "https:\/\/cdnjs.cloudflare.com\/ajax\/libs\/bootstrap\/5.3.3\/css\/bootstrap.min.css",
        "includes_location": "Head - End",
        "includes_type": "Stylesheet"
    }
}';

$includes_text = "";
$dir_settings = $settings->settings['out_dir'] . "/wmo/settings";
$dir_content = $settings->settings['out_dir'] . "/wmo/content";

$filename = "$dir_settings/includes.json";
$script_filename = "$dir_settings/scripts.json";

if (($useDB ?? "no") == "yes") {
    $includes = (db_entry_exists($filename, $conn)) ? json_decode(db_get_contents($filename, $conn), true) : json_decode($defaultjson, true);
} else {
    $includes = (file_exists($filename)) ? json_decode(file_get_contents($filename), true) : json_decode($defaultjson, true);
}


if (isset($_POST['includes_name'])) {
    if ($_POST['includes_type'] == "HTML") {
        echo "UM";
        $htmlcheck = new DOMDocument();
        libxml_use_internal_errors();
        if ($htmlcheck->loadXML($_POST['includes_url'])) {

        } else {
            $_POST['includes_url'] = "INVALID HTML: DELETE THIS AND TRY AGAIN";
        }
        libxml_clear_errors();
        libxml_use_internal_errors(false);
    } else {
        if (filter_var($_POST['includes_url'], FILTER_VALIDATE_URL)) {

        } else {
            $_POST['includes_url'] = "INVALID URL: DELETE THIS AND TRY AGAIN";
        }
    }
    $includes[] = [
        'includes_name' => $_POST['includes_name'],
        'includes_url' => $_POST['includes_url'],
        'includes_location' => $_POST['includes_location'],
        'includes_type' => $_POST['includes_type']
    ];

    if (($useDB ?? "no") == "yes") {
        db_put_contents($filename, json_encode($includes, JSON_PRETTY_PRINT), $conn);
    } else {
        file_put_contents($filename, json_encode($includes, JSON_PRETTY_PRINT));
    }

    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");

}

if (isset($_POST['delete'])) {
    foreach ($includes as $key => $value) {
        if ($value['includes_name'] == $_POST['delete']) {
            unset($includes[$key]);
        }
    }

    if (($useDB ?? "no") == "yes") {
        db_put_contents($filename, json_encode($includes, JSON_PRETTY_PRINT), $conn);
    } else {
        file_put_contents($filename, json_encode($includes, JSON_PRETTY_PRINT));
    }
    
    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");

}

if (isset($_POST['move'])) {
    foreach ($includes as $key => $value) {
        echo $value['includes_name'];
        if ($value['includes_name'] == $_POST['move']) {
            $move = $key - 1;

            if ($move >= 0) {
                $tmp = $includes[$move];
                $includes[$move] = $includes[$key];
                $includes[$key] = $tmp;
            }
        }

    }
    file_put_contents($filename, json_encode($includes, JSON_PRETTY_PRINT));
    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");
}

$includes[] = [
    'includes_name' => 'Palette',
    'includes_url' => '/css/palette.css',
    'includes_location' => 'Controled by Palette',
    'includes_type' => 'Stylesheet'
];
$haystack[] = 'Palette';
$includes[] = [
    'includes_name' => 'Fonts',
    'includes_url' => '/css/fonts.css',
    'includes_location' => 'Controled by Fonts',
    'includes_type' => 'Stylesheet'
];
$haystack[] = 'Fonts';

$scripts = json_decode(file_get_contents($script_filename), true);
foreach ($scripts as $key => $value) {
    $includes[] = [
        'includes_name' => ucfirst(str_replace(".js", "", $key)),
        'includes_url' => "/js/$key",
        'includes_location' => 'Controled by Scripts',
        'includes_type' => 'Script'
    ];
    $haystack[] = ucfirst(str_replace(".js", "", $key));

}


foreach ($includes as $key => $value) {
    $disabled = in_array($value['includes_name'], $haystack);
    $includes_text .= str_replace(
        ['#includes_name#', '#includes_url#', '#includes_location#', '#includes_type#', "#disabled#"],
        [$value['includes_name'], htmlspecialchars($value['includes_url']), $value['includes_location'], $value['includes_type'], ($disabled ? 'disabled="true"' : "")],
        $used_template
    );
}

$html_body = str_replace(
    ['#includes-used#'],
    [$includes_text],
    file_get_contents(__DIR__ . "/form.html")
);