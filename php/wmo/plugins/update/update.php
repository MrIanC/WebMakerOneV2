<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$dir_settings = $settings->settings['out_dir'] . "/wmo/settings";
$versionFilename = "$dir_settings/version.json";



$msg[] = "";

if (isset($_POST['update'])) {
    $go = true;

    $url = 'https://github.com/MrIanC/WebMakerOneV2/archive/refs/heads/master.zip';
    $zipFile = __DIR__ . '/install.zip';

    if ($go) {
        $fp = fopen($zipFile, 'w+');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 1024 * 1024);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_LOW_SPEED_LIMIT, 1024);
        curl_setopt($ch, CURLOPT_LOW_SPEED_TIME, 30);
        curl_setopt($ch, CURLOPT_ENCODING, '');

        curl_exec($ch);

        if (curl_errno($ch)) {
            $msg[] = 'Error: ' . curl_error($ch);
        } else {
            if (file_get_contents(__DIR__ . "/install.zip") == "Not Found") {
                $msg[] = 'Invalid Zip File!';
                $go = false;
            } else {
                $msg[] = 'File downloaded successfully!';
            }

        }

        curl_close($ch);
        fclose($fp);
    }
    if ($go) {
        $extractPath = $_SERVER['DOCUMENT_ROOT'] . "/app";

        $zip = new ZipArchive;
        if ($zip->open($zipFile) === TRUE) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filePath = $zip->getNameIndex($i);
                $mainFolder = strpos($filePath, '/') !== false ? explode('/', $filePath)[0] . '/' : '';
                $newPath = str_replace($mainFolder, '', $filePath);
                $extract = false;

                if (str_contains($newPath, "setup/"))
                    $extract = true;
                if (str_contains($newPath, "js/"))
                    $extract = true;
                if (str_contains($newPath, "settings.json"))
                    $extract = false;
                if (str_contains($newPath, "state.json"))
                    $extract = false;
                if (str_contains($newPath, "path.php"))
                    $extract = false;


                if ($extract == true) {
                    if (substr($filePath, -1) == '/') {
                        @mkdir("$extractPath/$newPath", 0755, true);
                    } else {
                        $msg[] = "extracting: $extractPath/$newPath </br>";
                        copy("zip://$zipFile#$filePath", "$extractPath/$newPath");
                    }
                }
            }
            $zip->close();
            unlink($zipFile);
            $msg[] = 'Files unzipped successfully!';

            $commits = json_decode(file_get_contents("https://api.github.com/repos/MrIanC/WebMakerOneV2/commits?sha=master&per_page=1", false, stream_context_create(['http' => ['header' => "User-Agent: PHP\r\n"]])), true);
            $gitDate = $commits[0]['commit']['author']['date'];
            db_put_contents($versionFilename, json_encode(["wmoV2" => $gitDate], JSON_PRETTY_PRINT & JSON_UNESCAPED_SLASHES), $conn);

        } else {
            $go = false;
            $msg[] = 'Failed to unzip the file!';
        }
    }
}

$commits = json_decode(file_get_contents("https://api.github.com/repos/MrIanC/WebMakerOneV2/commits?sha=master&per_page=1", false, stream_context_create(['http' => ['header' => "User-Agent: PHP\r\n"]])), true);
$gitDate = $commits[0]['commit']['author']['date'];

if (($useDB ?? "no") == "yes") {
    $version = (db_entry_exists($versionFilename, $conn)) ? json_decode(db_get_contents($versionFilename, $conn), true) : ["wmoV2" => "ORIGIN"];
} else {
    $version = (file_exists($versionFilename)) ? json_decode(file_get_contents($versionFilename), true) : ["wmoV2" => "ORIGIN"];
}

if ($gitDate != $version['wmoV2']) {
    $msg[] = "New Version Available!";
    
}




$html_body = str_replace(
    ['#version#', "#current_version#", "#logs#"],
    [$version['wmoV2'], $gitDate, create_log_from_array($msg ?? [])],
    file_get_contents(__DIR__ . "/form.html")
);
