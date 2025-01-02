<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$dir_settings = $settings->settings['out_dir'] . "/wmo/settings";
$versionFilename = "$dir_settings/version.json";
$dir_root = $_SERVER['DOCUMENT_ROOT'];
$msg[] = "";

$go = false;
if (($_POST['update'] ?? "false" == "Github")) {
    $msg = "Valid Update Request";
    $go = true;
}


if ($go) {
    $msg = "Downloading Update Zip file from git";

    $url = 'https://github.com/MrIanC/WebMakerOneV2/archive/refs/heads/master.zip';
    $zipFile = "$dir_root/install.zip";

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
    $extractPath = "$dir_root/app";
    $zip = new ZipArchive;

    if ($zip->open($zipFile) === TRUE) {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filePath = $zip->getNameIndex($i);
            $mainFolder = strpos($filePath, '/') !== false ? explode('/', $filePath)[0] . '/' : '';
            $newPath = str_replace($mainFolder, '', $filePath);
            $extract = true;

            if ($extract == true) {
                if (substr($filePath, -1) == '/') {
                    @mkdir("$extractPath/$newPath", 0755, true);
                } else {
                    $msg[] = "extracting: $extractPath/$newPath </br>";
                    if (copy("zip://$zipFile#$filePath", "$extractPath/$newPath")) {
                        $msg[] = "extracted: $extractPath/$newPath </br>";
                    } else {
                        $msg[] = "failed to extract: $extractPath/$newPath </br>";
                    }
                }
            }
        }
        $zip->close();
        if (unlink($zipFile)) {
            $msg[] = "Deleted Zip File";
        } else {
            $msg[] = "Failed to Delete Zip File";
        }

        $msg[] = 'Files unzipped ' . $i . ' successfully!';
        $msg[] = '<button id="continue" class="btn btn-primary">Continue</button>';

        $commits = json_decode(file_get_contents("https://api.github.com/repos/MrIanC/WebMakerOneV2/commits?sha=master&per_page=1", false, stream_context_create(['http' => ['header' => "User-Agent: PHP\r\n"]])), true);
        $gitDate = $commits[0]['commit']['author']['date'];

    } else {
        $go = false;
        $msg[] = 'Invalid Zip File!';
    }
}

if ($go) {
    $msg[] = "Updating Version Number";
    if (($useDB ?? "no") == "yes") {
        db_put_contents($versionFilename, json_encode(["wmoV2" => $gitDate], JSON_PRETTY_PRINT & JSON_UNESCAPED_SLASHES), $conn);
    } else {
        file_put_contents($versionFilename, json_encode(["wmoV2" => $gitDate], JSON_PRETTY_PRINT & JSON_UNESCAPED_SLASHES));
    }
}


if (($useDB ?? "no") == "yes") {
    $version = (db_entry_exists($versionFilename, $conn)) ? json_decode(db_get_contents($versionFilename, $conn), true) : ["wmoV2" => "ORIGIN"];
} else {
    $version = (file_exists($versionFilename)) ? json_decode(file_get_contents($versionFilename), true) : ["wmoV2" => "ORIGIN"];
}

$commits = json_decode(@file_get_contents("https://api.github.com/repos/MrIanC/WebMakerOneV2/commits?sha=master&per_page=1", false, stream_context_create(['http' => ['header' => "User-Agent: PHP\r\n"]])), true);

foreach ($http_response_header ?? [] as $header) {
    if (str_contains($header, "X-RateLimit-Remaining: 0")) {
        $msg[] = "Github API Rate Limit Reached!";
        $msg[] = "Please try again later.";
        $commits = null;
    }
}
if (isset($commits)) {
    $gitDate = $commits[0]['commit']['author']['date'] ?? "Not Available";
    if ($gitDate != $version['wmoV2']) {
        $msg[] = "New Version Available!";

    } else {
        $msg[] = "This Version is Up to Date!";
    }
} else {
    $gitDate = "Not Available";
}

$html_body = str_replace(
    ['#version#', "#current_version#", "#logs#", "#dlavail#"],
    [$version['wmoV2'], $gitDate, create_log_from_array($msg ?? []), $commits ? "" : 'disabled="true"'],
    file_get_contents(__DIR__ . "/form.html")
);
