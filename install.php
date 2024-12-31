<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_POST['setup'] ?? false == "true") {
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
                $extract = true;

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
            $msg = [];
            $msg[] = '<div class="mb-3">';
            $msg[] = 'Files unzipped ' . $i . ' successfully!';
            $msg[] = '</div>';
            $msg[] = '<button id="continue" class="btn btn-primary">Continue</button>';


            $commits = json_decode(file_get_contents("https://api.github.com/repos/MrIanC/WebMakerOneV2/commits?sha=master&per_page=1", false, stream_context_create(['http' => ['header' => "User-Agent: PHP\r\n"]])), true);
            $gitDate = $commits[0]['commit']['author']['date'];
            //db_put_contents($versionFilename, json_encode(["wmoV2" => $gitDate], JSON_PRETTY_PRINT & JSON_UNESCAPED_SLASHES), $conn);

        } else {
            $go = false;
            $msg[] = 'Failed to unzip the file!';
        }
    }
} else {
    $msg[] = '
                        <div>
                            <p>Download WebMakerOne from Github <br />and overwrite all content in "/app"?</p>
                        </div>
                        <form method="POST">
                            <button name="setup" value="setup" class="btn btn-primary">Download and Install</button>
                        </form>

                        <div id="progressBar" class="progress d-none" role="progressbar">
                            <div id="progress" class="progress-bar progress-bar-striped progress-bar-animated"
                                style="width: 0%"></div>
                        </div>
    
    ';

}
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <title>WebMakerOne Setup</title>
        <meta name="robots" content="index, follow">
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <link rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" />
    </head>

    <body>
        <div class="bg-dark-subtle bg-gradient">
            <div class="container">
                <div class="d-flex align-items-center justify-content-center min-vh-100">
                    <div class="border shadow rounded bg-white p-4 text-center">
                        <h1 class="fs-3 border-bottom">WebMakerOne Setup</h1>
                            <?php
                                echo implode($msg);
                            ?>
                    </div>
                </div>

            </div>
        </div>
        <script>
            $(document).ready(function () {
                $("form").submit(function (event) {
                    $(this).addClass("d-none");
                    $("#progressBar").removeClass("d-none");
                    $("#progress").css("width", "100%");
                });
                $("#continue").click(function() {
                    window.location = "/app/?wmo=users";
                })
            });
        </script>
    </body>

    </html>

    <?php
