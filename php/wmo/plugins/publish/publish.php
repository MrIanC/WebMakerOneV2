<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (!isset($_POST['deployment'])) {
    $html_body = file_get_contents(__DIR__ . "/form.html");
} else {
    switch ($_POST['deployment']) {
        case "construction":
            $html_body = str_replace("#logs#", implode(build_construction()), file_get_contents(__DIR__ . "/construction_results.html"));
            break;
        case "public":
            if (($useDB ?? "no") == "yes") {
                $html_body = str_replace("#logs#", build_public_from_db(), file_get_contents(__DIR__ . "/construction_results.html"));
            } else {
                $html_body = str_replace("#logs#", implode(build_public()), file_get_contents(__DIR__ . "/construction_results.html"));
            }
            break;
        case "zip_save":
            include __DIR__ . "/zip.php";
            $html_body = str_replace("#logs#", $log_string, file_get_contents(__DIR__ . "/construction_results.html"));
            break;
    }
}