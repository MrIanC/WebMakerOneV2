<?php
$files = [];
foreach(glob(__DIR__ . "/*") as $dir) {
    if (is_dir($dir)) {
        foreach(glob("$dir/*.html") as $file) {
            $id = str_replace([" ","-"], "_", basename($dir) . "_" . str_replace(".html","",basename($file)));
            $files[$id] = 
            [
                "path"=>str_replace($_SERVER['DOCUMENT_ROOT'],"", $file),
                "category"=> basename($dir),
                "label"=> ucwords(str_replace(".html","",basename($file)))
            ];
        }
    }
}

echo json_encode($files,JSON_PRETTY_PRINT);