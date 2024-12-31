<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


$dir_settings = $settings->settings['out_dir'] . "/wmo/settings/";
$datafilename = "$dir_settings/data.json";
$data = (file_exists($datafilename)) ? json_decode(file_get_contents($datafilename), true) : [];


$baseDir = $_SERVER['DOCUMENT_ROOT'];

$files = listFilesInDirectory($baseDir);
foreach ($files as $key => $file) {
    if (str_contains($file, "$baseDir/app")) {
        unset($files[$key]);
    }

}
$tmp = "";
foreach ($files as $key => $file) {
    $tmp .= basename($file) . ", ";
}

$logs[] = "[" . rtrim($tmp, ", ") . "] - " . count($files) . " files found in $baseDir \n";

$tmp = "";
$zipFileName = str_replace([".", " ", "-"], "_", $data['name']) . date("Ymd.his").".zip";

$zip = new ZipArchive();
if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    foreach ($files as $file) {
        if (file_exists($file)) {
            $localPath = str_replace($baseDir . "/", '', $file);
            $zip->addFile($file, $localPath);
            $tmp .= basename($file) . ", ";
            
        }
    }

    // Close the ZIP archive
    $zip->close();
    /*
        // Set headers to prompt download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zipFileName) . '"');
        header('Content-Length: ' . filesize($zipFileName));

        // Read the ZIP file to output it to the browser
        readfile($zipFileName);

        // Delete the temporary ZIP file after download
        unlink($zipFileName);
    */
    $logs[] = "[" . rtrim($tmp, ", ") . "] - " . count($files) . " added to ZIP file. \n";
    $logs[] = '<a href="' . $zipFileName . '">Download ZIP</a>';
    
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
    $filesize = round($filesize,2);
    $logs[]  = "$filesize $filesize_named"; // File size in bytes


} else {
    $logs[] = 'Failed to create ZIP file.';
}



$logsTmp = $logs;
$logs = [];
foreach ($logsTmp as $key => $value) {
    if (!is_string($value)) {
        continue; // Skip non-string values
    }

    $words = explode(" ", $value);
    $tmp = "";
    $chunks = [];

    foreach ($words as $word) {
        // Add word to the current line if it fits, else store the current line
        if (strlen($tmp . $word) + 1 > 60) { // +1 for the space
            $chunks[] = trim($tmp); // Trim to remove leading space
            $tmp = "";
        }
        $tmp .= " " . $word;
    }

    // Add the last chunk
    if (!empty($tmp)) {
        $chunks[] = trim($tmp);
    }

    // Append chunks to $logs
    foreach ($chunks as $chunk) {
        $logs[] = "$chunk\n";
    }
}
