<?php
$dir_settings = $settings->settings['out_dir'] . "/wmo/settings";
$dir_content = $settings->settings['out_dir'] . "/wmo/content";
$filename = "$dir_settings/scripts.json";

$defaultscriptdir = __DIR__ . "/default/*.js";
$scriptdir = "$dir_content/scripts/*";

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
    $scripts = (db_entry_exists($filename, $conn)) ? json_decode(db_get_contents($filename, $conn), true) : [];
} else {
    $scripts = (file_exists($filename)) ? json_decode(file_get_contents($filename), true) : [];
}


$script_list = "";

if (isset($_POST['save'])) {
    $tmp = $_POST;
    unset($tmp['save']);
    $scripts[$_POST['save']]['options'] = $tmp;

    if (($useDB ?? "no") == "yes") {
        db_put_contents($filename, json_encode($scripts, JSON_PRETTY_PRINT), $conn);
    } else {
        file_put_contents($filename, json_encode($scripts, JSON_PRETTY_PRINT));
    }    
    
    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");
}
if (isset($_POST['enable'])) {
    print_r($_POST);
    $tmp = $_POST;
    unset($tmp['enable']);
    $scripts[$_POST['enable']]['options'] = $tmp;
    $scripts[$_POST['enable']]['enabled'] = "true";

    if (($useDB ?? "no") == "yes") {
        db_put_contents($filename, json_encode($scripts, JSON_PRETTY_PRINT), $conn);
    } else {
        file_put_contents($filename, json_encode($scripts, JSON_PRETTY_PRINT));
    }    
    
    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");
}
if (isset($_POST['disable'])) {
    print_r($_POST);
    $tmp = $_POST;
    unset($tmp['disable']);
    $scripts[$_POST['disable']]['options'] = $tmp;
    $scripts[$_POST['disable']]['enabled'] = "false";

    if (($useDB ?? "no") == "yes") {
        db_put_contents($filename, json_encode($scripts, JSON_PRETTY_PRINT), $conn);
    } else {
        file_put_contents($filename, json_encode($scripts, JSON_PRETTY_PRINT));
    }    
    
    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");
}
if (isset($_POST['delete'])) {
    unset($scripts[$_POST['delete']]);

    if (($useDB ?? "no") == "yes") {
        db_put_contents($filename, json_encode($scripts, JSON_PRETTY_PRINT), $conn);
        db_unlink("$dir_content/scripts/{$_POST['delete']}",$conn);
    } else {
        file_put_contents($filename, json_encode($scripts, JSON_PRETTY_PRINT));
        unlink("$dir_content/scripts/{$_POST['delete']}");
    }    
    
    unlink(__DIR__ . "/default/{$_POST['delete']}");

    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");
}





foreach (glob($defaultscriptdir) as $files) {
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
                        name="' . $key . '" value="' . ($scripts[$filename]['options'][$key] ?? "") . '"/>
                    <label for="' . $key . '01">' . $value . '</label>
                </div>
            ';
        }
    }

    $startPos = strpos($fileContents, "/*startDescription");
    $endPos = strpos($fileContents, "endDescription*/");
    $description = "";
    if (!($startPos === false || $endPos === false)) {
        $startPos += strlen("/*startDescription");
        $description = substr($fileContents, $startPos, $endPos - $startPos);

    }
    $enabled = isset($scripts[$filename]['enabled']) ? ($scripts[$filename]['enabled'] == "true" ? '<button class="btn btn-primary btn-sm mb-1" name="disable" value="#scriptname#"><i class="bi bi-x-circle"></i></button>' : '<button class="btn btn-primary btn-sm mb-1" name="enable" value="#scriptname#"><i class="bi bi-check-circle"></i></button>') : '<button class="btn btn-primary btn-sm mb-1" name="enable" value="#scriptname#"><i class="bi bi-check-circle"></i></button>';
    $enabledText = isset($scripts[$filename]['enabled']) ? ($scripts[$filename]['enabled'] == "true" ? 'Enabled' : 'Disabled') : "Disabled";

    $script_list .= str_replace(
        ['#scriptname#', "#options#", "#description#", "#enabled#", "#enabledText#"],
        [$filename, $options, $description, $enabled, $enabledText],
        '
        <div class=" border shadow p-2 m-2 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-1 py-1">
                <div class="d-flex justify-content-between align-items-center">
                    <i class="fs-3 p-2 bi bi-filetype-js"></i>
                    <div>
                        <div>
                            #scriptname# <span class="text-secondary small">#enabledText#</span>
                        </div>
                        <div class="text-secondary small">
                            #description#
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
                        <button class="btn btn-primary btn-sm mb-1" name="delete" value="#scriptname#"><i class="bi bi-x-square"></i></button>
                        </div>
                        <div>
                            
                            ' . $enabled . '
                            <button class="btn btn-primary btn-sm mb-1" name="save" value="#scriptname#"><i class="bi bi-floppy"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        '
    );

    $src = [];
    $rep = [];

    foreach($scripts[$filename]['options']??[] as $key =>$value) {
        $src[] = "#$key#";
        $rep[] = $value;
    }

    $scriptFiles = "$dir_content/scripts/$filename";
    $editedScripts = str_replace($src, $rep, $fileContents);
    if (($useDB ?? "no") == "yes") {
        db_put_contents($scriptFiles,$editedScripts, $conn);
    } else {
        file_put_contents($scriptFiles, $editedScripts);
    }



}


$html_body = str_replace(
    ['#script-list#'],
    [$script_list],
    file_get_contents(__DIR__ . "/form.html")
);
