<?php

function question($question)
{
    include $_SERVER['DOCUMENT_ROOT'] . "/app/php/database/database.php";

    $dir_settings = dirname($_SERVER['DOCUMENT_ROOT']) . "/wmo/settings";
    $filename = "$dir_settings/apikeys.json";

    if (($useDB ?? "no") == "yes") {
        $apis = (db_entry_exists($filename, $conn)) ? json_decode(db_get_contents($filename, $conn), true) : [];
    } else {
        $apis = (file_exists($filename)) ? json_decode(file_get_contents($filename), true) : [];
    }
    
    
    $apiKey = $apis['gemini_ai'] ?? null;
    if (isset($apiKey)) {

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=" . $apiKey;

        $data = [
            "contents" => [
                [
                    "parts" => [
                        [
                            "text" => "Can you create in html a block that looks like this? make sure that the block's root tag is <section> make sure that you use bootstrap 5 and no custom css. only reply with the html" . $question
                        ],
                    ]
                ]
            ]
        ];

        $options = [
            "http" => [
                "header" => "Content-Type: application/json\r\n",
                "method" => "POST",
                "content" => json_encode($data)
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        if ($response === FALSE) {
            return false;
        }

        file_put_contents(__DIR__ . '/response.json', $response);
        $r = json_decode(file_get_contents(__DIR__ . '/response.json'), true);
        if (isset($r['candidates'][0]['content']['parts'][0]['text'])) {
            return $r['candidates'][0]['content']['parts'][0]['text'];
        }
    }
}

echo question($_POST['data']);