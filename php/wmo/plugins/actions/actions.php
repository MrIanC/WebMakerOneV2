<?php
$dir_settings = $settings->settings['out_dir'] . "/wmo/settings";
$dir_content = $settings->settings['out_dir'] . "/wmo/content";
$filename = "$dir_settings/actions.json";

$defaultactiondir = __DIR__ . "/default/*.js";
$actiondir = "$dir_content/actions/*";

if (!empty($_FILES['uploadedFiles']['name'][0])) {

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    foreach ($_FILES['uploadedFiles']['name'] as $key => $fileName) {
        $tmpName = $_FILES['uploadedFiles']['tmp_name'][$key];
        $filePath = __DIR__ . "/default/" . basename($fileName);

        if (move_uploaded_file($tmpName, $filePath)) {
            $message[] = "<p style='color:green;'>File '$fileName' uploaded successfully!</p>";
        } else {
            $message[] = "<p style='color:red;'>Failed to upload file '$fileName'.</p>";
        }
    }

    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");

}



if (($useDB ?? "no") == "yes") {
    $actions = (db_entry_exists($filename, $conn)) ? json_decode(db_get_contents($filename, $conn), true) : [];
} else {
    $actions = (file_exists($filename)) ? json_decode(file_get_contents($filename), true) : [];
}

$action_list = "";

if (isset($_POST['save'])) {
    $tmp = $_POST;
    unset($tmp['save']);
    $actions[$_POST['save']]['options'] = $tmp;

    if (($useDB ?? "no") == "yes") {
        db_put_contents($filename, json_encode($actions, JSON_PRETTY_PRINT), $conn);
    } else {
        file_put_contents($filename, json_encode($actions, JSON_PRETTY_PRINT));
    }


    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");
}
if (isset($_POST['enable'])) {
    $tmp = $_POST;
    unset($tmp['enable']);
    $actions[$_POST['enable']]['options'] = $tmp;
    $actions[$_POST['enable']]['enabled'] = "true";

    if (($useDB ?? "no") == "yes") {
        db_put_contents($filename, json_encode($actions, JSON_PRETTY_PRINT), $conn);
    } else {
        file_put_contents($filename, json_encode($actions, JSON_PRETTY_PRINT));
    }

    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");
}
if (isset($_POST['disable'])) {
    print_r($_POST);
    $tmp = $_POST;
    unset($tmp['disable']);
    $actions[$_POST['disable']]['options'] = $tmp;
    $actions[$_POST['disable']]['enabled'] = "false";

    if (($useDB ?? "no") == "yes") {
        db_put_contents($filename, json_encode($actions, JSON_PRETTY_PRINT), $conn);
    } else {
        file_put_contents($filename, json_encode($actions, JSON_PRETTY_PRINT));
    }

    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");
}

if (isset($_POST['delete'])) {
    unset($actions[$_POST['delete']]);

    if (($useDB ?? "no") == "yes") {
        db_put_contents($filename, json_encode($actions, JSON_PRETTY_PRINT), $conn);
        db_unlink("$dir_content/actions/{$_POST['delete']}", $conn);
    } else {
        file_put_contents($filename, json_encode($actions, JSON_PRETTY_PRINT));
        unlink("$dir_content/actions/{$_POST['delete']}");
    }

    unlink(__DIR__ . "/default/{$_POST['delete']}");

    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");
}





foreach (glob($defaultactiondir) as $files) {
    $filename = basename($files);
    $fileContents = file_get_contents($files);

    $text = "";
    $json = [];
    $options = "";


    $startPos = strpos($fileContents, "/*start");
    $endPos = strpos($fileContents, "end*/");
    if (!($startPos === false || $endPos === false)) {
        $startPos += strlen("/*start");
        $text = substr($fileContents, $startPos, $endPos - $startPos);
        $json = json_decode($text, true);
        foreach ($json as $key => $value) {
            $options .= '
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="' . $key . '01" 
                        name="' . $key . '" value="' . ($actions[$filename]['options'][$key] ?? "") . '"/>
                    <label for="' . $key . '01">' . $value . '</label>
                </div>
            ';
        }
    }

    $startPos = strpos($fileContents, "/*startDeactionion");
    $endPos = strpos($fileContents, "endDeactionion*/");
    $deactionion = "";
    if (!($startPos === false || $endPos === false)) {
        $startPos += strlen("/*startDeactionion");
        $deactionion = substr($fileContents, $startPos, $endPos - $startPos);

    }
    $enabled = isset($actions[$filename]['enabled']) ? ($actions[$filename]['enabled'] == "true" ? '<button class="btn btn-primary btn-sm mb-1" name="disable" value="#actionname#"><i class="bi bi-x-circle"></i></button>' : '<button class="btn btn-primary btn-sm mb-1" name="enable" value="#actionname#"><i class="bi bi-check-circle"></i></button>') : '<button class="btn btn-primary btn-sm mb-1" name="enable" value="#actionname#"><i class="bi bi-check-circle"></i></button>';
    $enabledText = isset($actions[$filename]['enabled']) ? ($actions[$filename]['enabled'] == "true" ? 'Enabled' : 'Disabled') : "Disabled";

    $action_list .= str_replace(
        ['#actionname#', "#options#", "#deactionion#", "#enabled#", "#enabledText#"],
        [$filename, $options, $deactionion, $enabled, $enabledText],
        '
        <div class=" border shadow p-2 m-2 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-1 py-1">
                <div class="d-flex justify-content-between align-items-center">
                    <i class="fs-3 p-2 bi bi-filetype-js"></i>
                    <div>
                        <div>
                            #actionname# <span class="text-secondary small">#enabledText#</span>
                        </div>
                        <div class="text-secondary small">
                            #deactionion#
                        </div>
                    </div>
                </div>    
            <div>    
                    
            </div>
            </div>
            <div class="row align-items-end mb-1 py-1">
                <form method="post" spellcheck="false">    
                    <div class="col-12">#options#</div>    
                    <div class="col-12 text-end d-flex justify-content-between">
                        <div>
                        <button class="btn btn-primary btn-sm mb-1" name="delete" value="#actionname#"><i class="bi bi-x-square"></i></button>
                        </div>
                        <div>
                            
                            ' . $enabled . '
                            <button class="btn btn-primary btn-sm mb-1" name="save" value="#actionname#"><i class="bi bi-floppy"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        '
    );

    $src = [];
    $rep = [];

    foreach ($actions[$filename]['options'] ?? [] as $key => $value) {
        $src[] = "#$key#";
        $rep[] = $value;
    }


    $actionFiles = "$dir_content/actions/$filename";
    $editedActions = str_replace($src, $rep, $fileContents);
    if (($useDB ?? "no") == "yes") {
        db_put_contents($actionFiles, $editedActions, $conn);
    } else {
        if (!is_dir("$dir_content/actions")) {
            mkdir("$dir_content/actions");
        }
        file_put_contents("$dir_content/actions/$filename", $editedActions);
    }




}

$html_body = str_replace(
    ['#action-list#'],
    [$action_list],
    file_get_contents(__DIR__ . "/form.html")
);
