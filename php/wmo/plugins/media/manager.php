<?php
$dir_settings = $settings->settings['out_dir'] . "/wmo/settings";
$dir_content = $settings->settings['out_dir'] . "/wmo/content";
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads";
$message = [];
$faviconSettings = "$dir_settings/favicon.json";

if (($useDB ?? "no") == "yes") {
    $faviconDetails = (db_entry_exists($faviconSettings, $conn)) ? json_decode(db_get_contents($faviconSettings, $conn), true) : [];
} else {
    $faviconDetails = (file_exists($faviconSettings)) ? json_decode(file_get_contents($faviconSettings), true) : [];
}


if (!isset($_GET['edit'])) {



    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_FILES['uploadedFiles']['name'][0])) {

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES['uploadedFiles']['name'] as $key => $fileName) {
                $tmpName = $_FILES['uploadedFiles']['tmp_name'][$key];
                $filePath = $uploadDir . "/" . basename($fileName);

                if (move_uploaded_file($tmpName, $filePath)) {
                    $message[] = "<p style='color:green;'>File '$fileName' uploaded successfully!</p>";
                } else {
                    $message[] = "<p style='color:red;'>Failed to upload file '$fileName'.</p>";
                }
            }

            $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("Location: $fullUri");

        }
        if (isset($_POST['delete'])) {
            unlink("$uploadDir/{$_POST['delete']}");

            $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("Location: $fullUri");

        }
        if (isset($_POST['favicon'])) {
            //copy(, "{$_SERVER['DOCUMENT_ROOT']}/favicon.ico");
            $iconFilename = "$uploadDir/{$_POST['favicon']}";
            $iconOrigin = file_get_contents($iconFilename);


            $faviconDetails = ["favicon" => "{$_POST['favicon']}"];

            if (($useDB ?? "no") == "yes") {
                db_put_contents($faviconSettings, json_encode($faviconDetails, JSON_PRETTY_PRINT), $conn);
                db_put_contents("{$_SERVER['DOCUMENT_ROOT']}/favicon.ico", $iconOrigin, $conn);
                file_put_contents("{$_SERVER['DOCUMENT_ROOT']}/favicon.ico", $iconOrigin);
            } else {
                file_put_contents($faviconSettings, json_encode($faviconDetails, JSON_PRETTY_PRINT));
                file_put_contents("{$_SERVER['DOCUMENT_ROOT']}/favicon.ico", $iconOrigin);
            }



            $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("Location: $fullUri");
        }

    }





    $filelist = "";
    foreach (glob($uploadDir . "/*") as $files) {
        $mimeType = mime_content_type($files);
        $fileContents = file_get_contents($files);
        $base64Data = base64_encode($fileContents);
        $k = "data:$mimeType;base64,$base64Data";
        $isicon = str_contains($mimeType, "icon") ? '<button class="btn btn-primary btn-sm mb-1" name="favicon" value="' . basename($files) . '" title="set as favicon"><i class="bi bi-star-fill"></i></button>' : "";
        $isiconSet = (($faviconDetails['favicon'] ?? "") == basename($files)) ? "favicon" : "";

        $filelist .= str_replace(
            ['#filename#', '#k#', "#set_as_favicon#", "#is_favicon#"],
            [basename($files), $k, $isicon, $isiconSet],
            '
            <div class="d-flex justify-content-between align-items-center my-1 py-1 border-bottom">
                <div>
                    <img style="height:64px" src="#k#"/>
                    <a href="?wmo=media&amp;edit=uploads/#filename#">
                        <span>#filename#</span>
                    </a>
                     <span class="small text-secondary">#is_favicon#</span>
                </div>

                <div>
                    #set_as_favicon#   
                    <button class="btn btn-primary btn-sm mb-1" name="delete" value="#filename#"><i class="bi bi-file-earmark-x"></i></button>
                </div>
            </div>
'
        );
    }
    $html_body = str_replace(
        ["#message#", '#media_uploaded#'],
        [implode($message), $filelist],
        file_get_contents(__DIR__ . "/form.html")
    );
} else {
    include __DIR__ . "/editor.php";
}