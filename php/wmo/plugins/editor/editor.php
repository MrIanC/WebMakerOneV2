<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Report all PHP errors
error_reporting(E_ALL);
include $_SERVER['DOCUMENT_ROOT'] . "/app/php/database/database.php";

$dir_settings = dirname($_SERVER['DOCUMENT_ROOT']) . "/wmo/settings";
$dir_content = dirname($_SERVER['DOCUMENT_ROOT']) . "/wmo/content";
$webdir = str_replace($_SERVER['DOCUMENT_ROOT'], "", __DIR__);
$includes_filename = "$dir_settings/includes.json";

$js_plugin = [];
$plugins_Opts = "";
$js_plugin_array = "";
$open_filename = "$dir_content/{$_GET['page']}";

/**
 * Get the current plugins
 * Add <script> tags to head
 * Create grapesjs Options
 */

foreach (glob(__DIR__ . "/plugin/*.js") as $jsfile) {
    $filename = basename($jsfile);
    $function_name = str_replace('.js', '', "" . basename($jsfile));
    $js_plugin[] = '<script src="' . $webdir . '/plugin/' . $filename . '"></script>';
    $js_plugin_array .= "'" . str_replace('.js', '', basename($jsfile)) . "', ";
    $plugins_Opts .= "'$function_name': {},";
}

$js_plugin_array = rtrim($js_plugin_array, ', ');
$plugins_Opts = rtrim($plugins_Opts, ",");

/**
 * Make sure $_GET['page'] is set and not empty
 * Get the content for the page
 */

if (isset($_GET['page'])) {
    $_GET['page'] = str_replace(" ", "-", $_GET['page']);
}
if (isset($_GET['page']) && !empty($_GET['page'])) {
    if (($useDB ?? "no") == "yes") {
        $current_content = html_entity_decode((db_entry_exists($open_filename, $conn)) ? db_get_contents($open_filename, $conn) : "<div>DB File does Not Exist</div>");
    } else {
        if (file_exists("$dir_content/{$_GET['page']}")) {
            $current_content = html_entity_decode(file_get_contents($open_filename));
        } else {
            $current_content = "<div>File does Not Exist</div>";
        }
    }
} else {
    $current_content = "<div>Warning!! There is a problem with the filename</div>";
}


/**
 * Get the styles that should be included and include the fake path to local styles
 */
$canvas_styles = "";
if (($useDB ?? "no") == "yes") {
    $includes = (db_entry_exists($includes_filename, $conn)) ? json_decode(db_get_contents($includes_filename, $conn), true) : [];
}else {
    $includes = (file_exists($includes_filename)) ? json_decode(file_get_contents($includes_filename), true) : [];
}

foreach ($includes as $key => $value) {
    if ($value['includes_type'] == "Stylesheet") {
        $canvas_styles .= "'{$value['includes_url']}',";
    }
}

if (($useDB ?? "no") == "yes") {
    foreach (db_glob("$dir_content/css/*.css",$conn) as $file) {
        $fcss = $webdir . "/fake_css_db.php?filename=" . basename($file);
        $canvas_styles .= "'$fcss',";
    }
} else {
    foreach (glob("$dir_content/css/*.css") as $file) {
        $fcss = $webdir . "/fake_css.php?filename=" . basename($file);
        $canvas_styles .= "'$fcss',";
    }
}
$canvas_styles = rtrim($canvas_styles, ",");


/**
 * Get all the uploaded Images in the uploads directory
 */

$asset_array = "";
foreach (glob("{$_SERVER['DOCUMENT_ROOT']}/uploads/*") as $file) {
    $asset_array .= "'/uploads/" . basename($file) . "',";

}
$asset_array = rtrim($asset_array, ",");

?>
<html>

<head>
    <meta charset="utf-8">
    <title><?php echo $_GET['page'] ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/grapesjs/0.21.13/css/grapes.min.css"
        integrity="sha512-wt37la6ckobkyOM0BBkCvrv+ozN/tGRe5BtR8DtGuxZ+m9kIy8B9hb8iLpzdrdssK2N07EMG7Tsw+/6uulUeyg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/grapesjs/0.21.13/grapes.min.js"
        integrity="sha512-vnAsqCtkvU3XqbVNK0pQQ6F8Q98PDpGMpts9I4AJdauEZQVbqZGvJdXfvdKEnLC4o7Z1YfnNzsx+V/+NXo/08g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <?php echo implode($js_plugin); ?>
    <style>
        body,
        html {
            height: 100%;
            margin: 0;
        }
    </style>
</head>

<body>
    <div id="gjs" style="height:0px; overflow:hidden">
        <?php echo $current_content; ?>
    </div>
    <script type="text/javascript">
        window.onload = () => {
            window.editor = grapesjs.init({
                height: '100%',
                showOffsets: true,
                noticeOnUnload: false,
                storageManager: false,
                container: '#gjs',
                fromElement: true,
                canvas: {
                    styles: [
                        <?php echo $canvas_styles; ?>
                    ]
                },
                plugins: [
                    <?php echo $js_plugin_array ?>
                ],
                pluginsOpts: {
                    <?php echo $plugins_Opts; ?>
                },
                assetManager: {
                    assets: [
                        <?php echo $asset_array; ?>
                    ],
                }
            });
        }        
    </script>
</body>

</html>