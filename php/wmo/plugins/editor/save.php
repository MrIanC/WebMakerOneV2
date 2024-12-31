<?php

$pagename = $_POST['page'];
$html = $_POST['html'];
$css = $_POST['css'];
$dir_content = dirname($_SERVER['DOCUMENT_ROOT']) . "/wmo/content";

$CssRules = [];
// Get rid of #tags that don't belong
$whitespace = ["\t", "\n", " ", "\r"];
foreach (explode("}", $css) as $key => $value) {
    if (!empty($value)) {
        $rule = trim(str_replace($whitespace, "", $value) . "}");
        $selector = explode("{", $rule)[0];
        if ($selector[0] == "#")
            $CssRules[$selector] = $rule;
    }
}

$css = "";
foreach ($CssRules as $CssSelector => $value) {
    if (str_contains($html, ltrim($CssSelector, "#"))) {
        $css .= $value . "\n";
    }
}

$dom = new DOMDocument();
libxml_use_internal_errors(true);

$dom->loadXML($html);
libxml_clear_errors();

$bodyContent = '';
$body = $dom->getElementsByTagName('body')->item(0);
if ($body) {
    foreach ($body->childNodes as $child) {
        $bodyContent .= $dom->saveHTML($child);
    }
}

$content = "<style>$css</style>" . str_replace(['<body', '</body'], ['</div', '</div'], $bodyContent);
$savepage = "$dir_content/$pagename";

ini_set(option: 'display_errors', value: 1);

if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/app/php/database/database.php")) {
    include $_SERVER['DOCUMENT_ROOT'] . "/app/php/database/database.php";
}

$makebackupfilename = (str_contains($savepage, ".bak")) ? $savepage : $savepage . date("YmdHis") . ".bak";

if (($useDB ?? "no") == "yes") {
    if (db_entry_exists($savepage, $conn)) {
        $tmp = db_get_contents($savepage, $conn);

        db_put_contents($makebackupfilename, $tmp, $conn);
    }

    db_put_contents($savepage, $content, $conn);
    if (db_entry_exists($savepage, $conn)) {
        $tmp = db_get_contents($savepage, $conn);
        if ($tmp == $content) {
            echo "Saved";
        } else {
            echo "Something went Wrong. The content saved and the current content do not match";
        }

    } else {
        echo "Something went Wrong. Cant find the written db entry";
    }

} else {
    if (file_exists($savepage)) {
        copy($savepage, $makebackupfilename);
    }

    if (file_put_contents($savepage, $content)) {
        echo "Saved";
    } else {
        echo "Something went Wrong";
        file_put_contents(__DIR__ . "/temp" . date("YMDHi") . ".html", $content);
    }

}

