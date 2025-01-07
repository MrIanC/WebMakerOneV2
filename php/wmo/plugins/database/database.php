<?php

if ($_POST['export'] ?? "no" == "yes") {
    $zip = new ZipArchive();
    $zipFileName = "{$_SERVER['DOCUMENT_ROOT']}/export.zip";
    if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        exit("Cannot open <$zipFileName>\n");
    }
    foreach (db_glob("*", $conn) as $key => $value) {
        if (str_contains($value, "/wmo/")) {
            $position = strpos($value, "wmo");
            $fileName = substr($value, $position);
            $fileContents = "";
            $fileContents = db_get_contents($value, $conn);
            $zip->addFromString($fileName, $fileContents);
        } else {

        }
    }
    foreach (glob("{$_SERVER['DOCUMENT_ROOT']}/uploads/*") as $key => $value) {
        if (str_contains($value, "/uploads/")) {
            $position = strpos($value, "uploads");
            $fileName = substr($value, $position);
            $fileContents = "";
            $fileContents = file_get_contents($value);
            $zip->addFromString($fileName, $fileContents);
        }
    }
    if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/favicon.ico")) {
        $value = "{$_SERVER['DOCUMENT_ROOT']}/favicon.ico";
        $fileName = "favicon.ico";
        $fileContents = file_get_contents($value);
        $zip->addFromString($fileName, $fileContents);

    }
    $zip->close();

    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . "/export.zip";
    header("Location: $fullUri");
}

$file_upload_msg = [];
if ($_POST['import'] ?? "no" == "yes") {
    echo "Import Clicked";
    $go = false;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo "method is post";
        if (!empty($_FILES['uploadedFile']['name'])) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'];
            $fileName = $_FILES['uploadedFile']['name'];
            $tmpName = $_FILES['uploadedFile']['tmp_name'];
            $filePath = $uploadDir . "/" . basename($fileName);

            if (move_uploaded_file($tmpName, $filePath)) {
                $file_upload_msg[] = "<p style='color:green;'>File '$fileName' uploaded successfully!</p>";
                $go = true;
            } else {
                $file_upload_msg[] = "<p style='color:red;'>Failed to upload file '$fileName'.</p>";
                $go = false;
            }
        }

        if ($go) {
            $zip = new ZipArchive;
            $dirName= $_SERVER['DOCUMENT_ROOT'];
            if ($zip->open($filePath) === TRUE) {

                if (!is_dir("{$_SERVER['DOCUMENT_ROOT']}/uploads")) {
                    mkdir("{$_SERVER['DOCUMENT_ROOT']}/uploads", 0777, true);
                }

                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $fileInfo = $zip->statIndex($i);

                    if (str_contains($fileInfo['name'], "uploads/")) {
                        $fileContents = $zip->getFromName($fileInfo['name']);
                        file_put_contents("{$_SERVER['DOCUMENT_ROOT']}/{$fileInfo['name']}", $fileContents);
                    }

                    if (str_contains($fileInfo['name'], "wmo/")) {
                        $fileContents = $zip->getFromName($fileInfo['name']);
                        db_put_contents("$dirName/{$fileInfo['name']}", $fileContents, $conn);
                    }
                }
                $zip->close();
            } else {
                $go = false;
                $msg[] = 'Invalid Zip File!';
            }
        }

    }
}

$count_files = count(db_glob("*", $conn));
$html_body = str_replace(
    [
        '#database_name#',
        '#database_username#',
        '#database_password#',
        '#database_server#',
        "#count_files#",
        "#file_upload_msg#"
    ],
    [
        $dbname,
        $username,
        $password,
        $servername,
        $count_files,
        implode($file_upload_msg)
    ],
    file_get_contents(__DIR__ . "/form.html")
);