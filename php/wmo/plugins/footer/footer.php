<?php
$dir_content = $settings->settings['out_dir'] . "/wmo/content/";


$filename = "$dir_content/footer/footer.html";

$footer_exists = "Footer file does not exist";
if (file_exists($filename)) {
    $footer_exists = "Footer file exists";
}



$html_body =  str_replace(
['#footer-exists#'],
[$footer_exists],
file_get_contents(__DIR__ . "/form.html")
);


