<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


$dir_settings = $settings->settings['out_dir'] . "/wmo/settings";
$datafilename = "$dir_settings/data.json";
$baseDir = $_SERVER['DOCUMENT_ROOT'];
$go = true;
$logs = [];

if (($useDB ?? "no") == "yes") {
    $data = (db_entry_exists($datafilename, $conn)) ? json_decode(db_get_contents($datafilename, $conn), true) : [];
} else {
    $data = (file_exists($datafilename)) ? json_decode(file_get_contents($datafilename), true) : [];
}

if (isset($data['name'])) {
    $logs[] = "Name is set to {$data['name']}";
    if (empty($data['name'])) {
        $logs[] = "Name is empty";
        $go = false;
    }

} else {
    $logs[] = "Name is not set";
    $go = false;
}

if ($go) { //Get all files in the directory
    $tmp = "";
    $files = listFilesInDirectory($baseDir);
    foreach ($files as $key => $file) {
        if (str_contains($file, "$baseDir/app")) {
            unset($files[$key]);
        } else {
            $tmp .= basename($file) . ", ";
        }
    }
    $logs[] = "[" . rtrim($tmp, ", ") . "] - " . count($files) . " files found in $baseDir \n";
}

if ($go) { //Create the zip file
    $tmp = "";
    $zipFileName = $_SERVER['DOCUMENT_ROOT'] . "/" . str_replace([".", " ", "-"], "_", subject: $data['name']) . date("Ymd.his") . ".zip";

    $zip = new ZipArchive();
    if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        foreach ($files as $file) {
            if (file_exists($file)) {
                $localPath = str_replace($baseDir . "/", '', $file);
                $zip->addFile($file, $localPath);
                $tmp .= basename($file) . ", ";

            }
        }

        $zip->close();
        $logs[] = "[" . rtrim($tmp, ", ") . "] - " . count($files) . " added to ZIP file. \n";
        $logs[] = '<a href="/' . basename($zipFileName) . '">Download ZIP</a>';

        $filesize = filesize($zipFileName);
        $filesize_named = "bytes";
        if ($filesize > 1024) {
            $filesize = $filesize / 1024;
            $filesize_named = "kb";
        }
        if ($filesize > 1024) {
            $filesize = $filesize / 1024;
            $filesize_named = "mb";
        }
        if ($filesize > 1024) {
            $filesize = $filesize / 1024;
            $filesize_named = "gb";
        }
        $filesize = round($filesize, 2);
        $logs[] = "$filesize $filesize_named"; // File size in bytes


    } else {
        $logs[] = 'Failed to create ZIP file.';
        $go = false;
    }
}

$log_string = create_log_from_array($logs);


