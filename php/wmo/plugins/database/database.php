<?php

if ($_POST['export'] ?? "no" == "yes") {
    $zip = new ZipArchive();
    $zipFileName = "{$_SERVER['DOCUMENT_ROOT']}/example.zip";
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
}



$count_files = count(db_glob("*", $conn));
$html_body = str_replace(
    [
        '#database_name#',
        '#database_username#',
        '#database_password#',
        '#database_server#',
        "#count_files#"
    ],
    [
        $dbname,
        $username,
        $password,
        $servername,
        $count_files
    ],
    file_get_contents(__DIR__ . "/form.html")
);