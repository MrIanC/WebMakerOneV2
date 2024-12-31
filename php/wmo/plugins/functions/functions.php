<?php

function listFilesInDirectory($directory): array
{
    $files = [];
    $iterator = new DirectoryIterator($directory);
    foreach ($iterator as $fileinfo) {
        if ($fileinfo->isDot()) {
            continue;
        }
        if ($fileinfo->isDir()) {
            if (!in_array(basename($fileinfo->getPathname()), ['setup'])) {
                $files = array_merge($files, listFilesInDirectory($fileinfo->getPathname()));
            }
        } else {
            $files[] = $fileinfo->getPathname();
        }
    }
    return $files;
}

function findEmptyDirs($path)
{
    $emptyDirs = [];

    // Get all files and directories inside the current directory
    $items = array_diff(scandir($path), ['.', '..']);

    if (empty($items)) {
        // If the directory is empty, add it to the result
        $emptyDirs[] = $path;
    } else {
        foreach ($items as $item) {
            $fullPath = $path . DIRECTORY_SEPARATOR . $item;
            if (is_dir($fullPath)) {
                // Recursively search in subdirectories
                $emptyDirs = array_merge($emptyDirs, findEmptyDirs($fullPath));
            }
        }
    }

    return $emptyDirs;
}
function build_public_from_db()
{
    $startBytes = memory_get_usage();

    global $settings;
    global $conn;
    $baseDir = $_SERVER['DOCUMENT_ROOT'];

    $dir_settings = $settings->settings['out_dir'] . "/wmo/settings";
    $dir_content = $settings->settings['out_dir'] . "/wmo/content";

    $go = true;
    $logs[0] = false;

    $logs[] = "<b>Building for Deployment from DB</b>";

    if ($go) { // Get and Delete currently deployed files
        $logs[] = "<b>Get and Delete currently deployed files</b>";
        $files = listFilesInDirectory($baseDir);
        $files = array_filter($files, function ($file) use ($baseDir) {
            return !str_contains($file, "$baseDir/app") && !str_contains($file, "$baseDir/uploads");
        });

        $tmp = "[";
        foreach ($files as $key => $file) {
            $tmp .= basename($file) . ", ";
            unlink($file);
        }
        $logs[] = rtrim($tmp, ", ") . "] - Deleted Files";
        $logs[] = (memory_get_usage() - $startBytes) . " bytes";
        $logs[] = "";
        unset($files, $tmp, $file);
    }
    if ($go) {// Get and Delete currently deployed Directories
        $logs[] = "<b>Get and Delete currently deployed Directories</b>";
        $dirs = findEmptyDirs($baseDir);
        $dirs = array_filter($dirs, function ($dirs) use ($baseDir) {
            return !str_contains($dirs, "$baseDir/app") && !str_contains($dirs, "$baseDir/uploads");
        });
        $tmp = "[";
        foreach ($dirs as $key => $dir) {
            $tmp .= basename($dir) . ", ";
            rmdir($dir);
        }
        $logs[] = rtrim($tmp, ", ") . "] - Deleted Directories";
        $logs[] = (memory_get_usage() - $startBytes) . " bytes";
        $logs[] = "";
        unset($dirs, $dir, $tmp);
    }
    if ($go) {// Load required Settings to Variables
        $logs[] = "<b>Load Settings</b>";
        //settings
        $includes_filename = "$dir_settings/includes.json";
        $script_filename = "$dir_settings/scripts.json";
        $action_filename = "$dir_settings/actions.json";
        $navigation_filename = "$dir_settings/navigation.json";
        $header_filename = "$dir_settings/navigation.json";
        $landing_filename = "$dir_settings/landing.json";
        $favicon_filename = "$dir_settings/favicon.json";
        $seo_filename = "$dir_settings/seo.json";
        
        //files
        $fonts_filename = "$dir_content/css/fonts.css";
        $palette_filename = "$dir_content/css/palette.css";
        $header_html_filename = "$dir_content/header/header.html";
        $footer_html_filename = "$dir_content/footer/footer.html";
        $jsonld_data_filename ="$dir_settings/jsonld.json";

        //settings
        $includes = (db_entry_exists($includes_filename, $conn)) ? json_decode(db_get_contents($includes_filename, $conn), true) : [];
        $scripts = (db_entry_exists($script_filename, $conn)) ? json_decode(db_get_contents($script_filename, $conn), true) : [];
        $actions = (db_entry_exists($action_filename, $conn)) ? json_decode(db_get_contents($action_filename, $conn), true) : [];
        $navigation = (db_entry_exists($navigation_filename, $conn)) ? json_decode(db_get_contents($navigation_filename, $conn), true) : [];
        $header = (db_entry_exists($header_filename, $conn)) ? json_decode(db_get_contents($header_filename, $conn), true) : [];
        $landing = (db_entry_exists($landing_filename, $conn)) ? json_decode(db_get_contents($landing_filename, $conn), true) : [];
        $favicon = (db_entry_exists($favicon_filename, $conn)) ? json_decode(db_get_contents($favicon_filename, $conn), true) : [];
        $seo = (db_entry_exists($seo_filename, $conn)) ? json_decode(db_get_contents($seo_filename, $conn), true) : [];

        //files
        $fonts_file = (db_entry_exists($fonts_filename, $conn)) ? db_get_contents($fonts_filename, $conn) : "";
        $palette_file = (db_entry_exists($palette_filename, $conn)) ? db_get_contents($palette_filename, $conn) : "";
        $header_file = (db_entry_exists($header_html_filename, $conn)) ? db_get_contents($header_html_filename, $conn) : "";
        $footer_file = (db_entry_exists($footer_html_filename, $conn)) ? db_get_contents($footer_html_filename, $conn) : "";
        
        //sortof files... decodes and encodes thing
        $jsonld_data = (db_entry_exists($jsonld_data_filename, $conn)) ? json_decode(db_get_contents($jsonld_data_filename, $conn), true) : [];
        
        //generated
        $landingpagename = "/" . str_replace(".html", "", ($landing['landing'] ?? ""));

        $menuArray = [];

        foreach ($navigation as $value) {
            $navigationArray[] = $value['navurl'];
            $menu[$value['navtext']] = $value['navurl'];
        }

        foreach ($includes as $key => $value) {
            $includes_html[$value['includes_location']][] = match ($value['includes_type']) {
                "Stylesheet" => '<link rel="stylesheet" href="' . $value['includes_url'] . '" />',
                "Script" => '<script src="' . $value['includes_url'] . '"></script>',
                "HTML" => $value['includes_url'],
                default => null, // Optional: handle unexpected cases if necessary
            };
        }

        $stylelinks = [];
        $scriptlinks = [];

        if (($checks ?? true) == true) {
            $tmp = "[";
            if (empty($includes)) {
                $go = false;
                $logs[] = "No sncludes. Fatal Error.";
            } else {
                $tmp .= "includes, ";
            }
            if (empty($scripts)) {
                $go = false;
                $logs[] = "No scripts. Fatal Error.";
            } else {
                $tmp .= "scripts, ";
            }
            if (empty($actions)) {
                $go = false;
                $logs[] = "No actions. Fatal Error.";
            } else {
                $tmp .= "actions, ";
            }
            if (empty($navigation)) {
                $go = false;
                $logs[] = "No navigation. Fatal Error.";
            } else {
                $tmp .= "navigation, ";
            }
            if (empty($menu)) {
                $go = false;
                $logs[] = "Empty Navigation. Fatal Error.";
            } else {
                $tmp .= "menu, ";
            }
            if (empty($seo)) {
                $go = false;
                $logs[] = "Empty Seo. Fatal Error.";
            } else {
                $tmp .= "seo, ";
            }

            if (empty($favicon)) {
                $go = false;
                $logs[] = "Empty favicon. Fatal Error.";
            } else {
                $tmp .= "favicon, ";
            }

            if (empty($fonts_file)) {
                $go = false;
                $logs[] = "No fonts.css. Fatal Error.";
            } else {
                $tmp .= "fonts.css, ";
            }
            if (empty($palette_file)) {
                $go = false;
                $logs[] = "No palette.css. Fatal Error.";
            } else {
                $tmp .= "palette.css, ";
            }
            if (empty($header)) {
                $go = false;
                $logs[] = "No header. Fatal Error.";
            } else {
                $tmp .= "header, ";
            }
            if (empty($landing)) {
                $go = false;
                $logs[] = "No landing. Fatal Error.";
            } else {
                $tmp .= "landing, ";
            }
            if (empty($header_file)) {
                $go = false;
                $logs[] = "No header File. Fatal Error.";
            } else {
                $tmp .= "header/header.html, ";
            }
            if (empty($footer_file)) {
                $go = false;
                $logs[] = "No footer File. Fatal Error.";
            } else {
                $tmp .= "footer/footer.html, ";
            }
            if (empty($jsonld_data)) {
                $go = false;
                $logs[] = "No Site Data. Fatal Error.";
            } else {
                $tmp .= "json, ";
            }
            

            $logs[] = rtrim($tmp, ", ") . "] - Settings Loaded";
        }

        $logs[] = (memory_get_usage() - $startBytes) . " bytes";
        $logs[] = "";
        unset($tmp);
    }
    if ($go) {// Find Entries in for $dir_content/pages (All content Pages) and Create Directories. generate $pages and $content_dirs
        $logs[] = "<b>Finding All Pages and Creating Directories</b>";
        $pages_found = false;
        $tmp = "[";
        $tmp2 = "[";
        foreach (db_glob("$dir_content/pages/*.html", $conn) as $page) {
            $pagename = str_replace(".html", "", basename($page));
            $dir = "$baseDir/$pagename";
            $tmp .= "$pagename, ";
            if (!is_dir($dir)) {
                if (mkdir($dir)) {
                    //$tmp2 .= "$pagename, ";
                    $tmp2 .= "$pagename, ";

                } else {
                    $logs[] = "Could not create page directories, Fatal Error. Check Write Permissions on document root";
                    $tmp2 .= "$pagename, ";
                    $go = false;
                }
                ;
            }
            $content_dirs[$page] = $dir;
            $pages[] = $page;
            $pages_found = true;
        }
        if (!is_dir("$baseDir/css")) {
            mkdir("$baseDir/css");
            $tmp2 .= "css, ";
        }
        if (!is_dir("$baseDir/js")) {
            mkdir("$baseDir/js");
            $tmp2 .= "css, ";
        }
        if (!is_dir("$baseDir/js/action")) {
            mkdir("$baseDir/js/action");
            $tmp2 .= "js/action, ";
        }

        $logs[] = rtrim($tmp, ", ") . '] - Pages Found';
        $logs[] = rtrim($tmp2, ", ") . '] - Directories Created';
        $logs[] = (memory_get_usage() - $startBytes) . " bytes";
        $logs[] = "";
        $go = $pages_found;
    }
    if ($go) { //create font.css and palette.css. store html link in $stylelinks
        $logs[] = "<b>Create Custom CSS Files</b>";
        $tmp = "[";
        if (file_put_contents("$baseDir/css/fonts.css", $fonts_file)) {
            $tmp .= "fonts.css, ";
        } else {
            $logs[] = "Fonts Failed. Fatal Error";
            $go = false;
        }

        if (file_put_contents("$baseDir/css/palette.css", $palette_file)) {
            $tmp .= "palette.css, ";
        } else {
            $logs[] = "Palette Failed. Fatal Error";
            $go = false;
        }
        $stylelinks['fonts'] = '<link rel="stylesheet" href="/css/fonts.css">';
        $stylelinks['palette'] = '<link rel="stylesheet" href="/css/palette.css">';

        $logs[] = rtrim($tmp, ", ") . '] - Custom CSS Created';
        $logs[] = (memory_get_usage() - $startBytes) . " bytes";
        $logs[] = "";
        unset($palette_file, $fonts_file, $tmp);
    }
    if ($go) { // create Custom Scripts. store Script tags in scriptlinks
        $logs[] = "<b>Getting Custom Scripts</b>";
        $tmp = "";
        $tmp2 = "";
        foreach ($scripts as $key => $value) {
            if ((($value['enabled'] ?? "false") == "true") ? true : false) {
                $scriptlinks[$key] = '<script src="/js/' . $key . '"></script>';
                $scriptContents = db_get_contents("$dir_content/scripts/$key", $conn);
                if (file_put_contents("$baseDir/js/$key", $scriptContents)) {
                    $tmp .= "$key, ";
                } else {
                    $logs[] = "Failed to find $key. Fatal Error";
                    $go = false;
                }
            } else {
                $tmp2 .= "$key, ";
            }
        }
        $logs[] = '[' . rtrim($tmp, ", ") . '] - Created Files';
        $logs[] = '[' . rtrim($tmp2, ", ") . '] - Skipped Files';
        $logs[] = (memory_get_usage() - $startBytes) . " bytes";
        $logs[] = "";

    }
    if ($go) { // create Action Scripts.
        if ((($scripts['action.js']['enabled'] ?? "false") == "true") ? true : false) {
            $logs[] = "<b>Getting Action Scripts</b>";
            $tmp = "";
            $tmp2 = "";

            foreach ($actions as $key => $value) {
                if ((($value['enabled'] ?? "false") == "true") ? true : false) {
                    $scriptContents = db_get_contents("$dir_content/actions/$key", $conn);
                    if (file_put_contents("$baseDir/js/action/$key", $scriptContents)) {
                        $tmp .= "$key, ";
                    } else {
                        $logs[] = "Failed to find $key. Fatal Error";
                        $go = false;
                    }
                } else {
                    $tmp2 .= "$key, ";
                }
            }
            $logs[] = '[' . rtrim($tmp, ", ") . '] - Created Files';
            $logs[] = '[' . rtrim($tmp2, ", ") . '] - Skipped Files';
            $logs[] = (memory_get_usage() - $startBytes) . " bytes";
            $logs[] = "";

        } else {
            $logs[] = "<b>Action Scripts Skipped. Not in Custom Scripts</b>";
            $logs[] = (memory_get_usage() - $startBytes) . " bytes";
            $logs[] = "";

        }
    }
    if ($go) { // Check and copy favicon
        $logs[] = "<b>Check and copy favicon</b>";
        if (isset($favicon['favicon']) && file_exists("$baseDir/uploads/{$favicon['favicon']}")) {
            if (copy("$baseDir/uploads/{$favicon['favicon']}", "$baseDir/favicon.ico")) {
                $logs[] = "[favicon.ico] - Copied";
            } else {
                $logs[] = "Favicon Not Found. Non-fatal Error";
            }
        }
        $logs[] = (memory_get_usage() - $startBytes) . " bytes";
        $logs[] = "";

    }
    if ($go) { // build and store the header from $pages while crops referencing the $navigation
        $logs[] = "<b>Building Navigation</b>";
        $tmp = "";
        foreach ($pages as $page) {
            $pagename = "/" . str_replace(".html", "", basename($page));
            $tmp .= "$pagename, ";
            $doc = new DOMDocument('1.0', "UTF-8");
            libxml_use_internal_errors(true); // Suppress warnings due to malformed HTML
            $doc->loadHTML('<header id="realheader">' . $header_file . "</header>");
            libxml_clear_errors();
            $templateLiActive = $doc->getElementById('templateActive');
            $templateLi = $doc->getElementById('template');
            $menuItemsUl = $doc->getElementById('menuItems');
            $header = $doc->getElementById("realheader");

            foreach ($menu as $title => $href) {
                $tmp_pagename = ($href == "/") ? $landingpagename : $href;
                $newLi = ($pagename == $tmp_pagename) ? $templateLiActive->cloneNode(true) : $templateLi->cloneNode(true);
                $link = $newLi->getElementsByTagName('a')->item(0);
                $link->setAttribute('href', $href);
                $link->textContent = $title;
                $menuItemsUl->appendChild($newLi);
            }
            $menuItemsUl->removeChild($templateLiActive);
            $menuItemsUl->removeChild($templateLi);
            $navigation[$pagename] = (function ($header, $dom) {
                $innerHtml = "";
                foreach ($header->childNodes as $child) {
                    $innerHtml .= $dom->saveHTML($child);
                }
                return $innerHtml;
            })($header, $doc);
        }
        $logs[] = '[' . rtrim($tmp, ", ") . '] - Navigation Built';
        $logs[] = (memory_get_usage() - $startBytes) . " bytes";
        $logs[] = "";
    }
    if ($go) { //build all html pages
        $logs[] = "<b>Building Html Files</b>";
        $tmp = '';
        $tmp3 = '';
        foreach ($pages as $page) {

            $menuName = str_replace(".html", "", basename($page));

            $HTML = [];
            $HTML[] = '<!DOCTYPE html>';
            $HTML[] = '<html lang="en">';
            $HTML[] = '';
            $HTML[] = '<head>';

            // SEO STUFF
            $seotag = "title";
            $HTML[] = (isset($seo[$menuName][$seotag]) && !empty(isset($seo[$menuName][$seotag]))) ? '<title>' . $seo[$menuName]['title'] . '</title>' : "<title>NO TITLE</title>";
            $seotag = "faviconUrl";
            $HTML[] = (isset($seo[$menuName][$seotag]) && !empty(isset($seo[$menuName][$seotag]))) ? '<link rel="icon" href="' . $seo[$menuName]['faviconUrl'] . '" type="image/x-icon">' : "";
            $seotag = "robotsMeta";
            $HTML[] = (isset($seo[$menuName][$seotag]) && !empty(isset($seo[$menuName][$seotag]))) ? '<meta name="robots" content="' . $seo[$menuName]['robotsMeta'] . '">' : "";
            $seotag = "metaDescription";
            $HTML[] = (isset($seo[$menuName][$seotag]) && !empty(isset($seo[$menuName][$seotag]))) ? '<meta name="description" content="' . $seo[$menuName]['metaDescription'] . '">' : "";
            $seotag = "canonicalUrl";
            $HTML[] = (isset($seo[$menuName][$seotag]) && !empty(isset($seo[$menuName][$seotag]))) ? '<meta rel="canonical" href="' . $seo[$menuName]['canonicalUrl'] . '">' : "";
            $seotag = "ogTitle";
            $HTML[] = (isset($seo[$menuName][$seotag]) && !empty(isset($seo[$menuName][$seotag]))) ? '<meta property="og:title" content="' . $seo[$menuName]['ogTitle'] . '" />' : "";
            $seotag = "ogDescription";
            $HTML[] = (isset($seo[$menuName][$seotag]) && !empty(isset($seo[$menuName][$seotag]))) ? '<meta property="og:desctiption" content="' . $seo[$menuName]['ogDescription'] . '" />' : "";
            $seotag = "canonicalUrl";
            $HTML[] = (isset($seo[$menuName][$seotag]) && !empty(isset($seo[$menuName][$seotag]))) ? '<meta property="og:url" content="' . $seo[$menuName]['canonicalUrl'] . '" />' : "";
            $seotag = "ogImage";
            $HTML[] = (isset($seo[$menuName][$seotag]) && !empty(isset($seo[$menuName][$seotag]))) ? '<meta property="og:image" content="' . $seo[$menuName]['ogImage'] . '" />' : "";

            // Top of Head includes_html
            foreach ($includes_html['Head - Start'] ?? [] as $val) {
                $HTML[] = $val;
            }


            $HTML[] = $stylelinks['fonts'];
            $HTML[] = $stylelinks['palette'];

            // Script Tags
            foreach ($scriptlinks ?? [] as $val) {
                $HTML[] = $val;
            }
            $HTML[] = '<script type="application/ld+json">';
            $HTML[] = $seo[$menuName]['schemaMarkup'] ?? "{}";
            $HTML[] = '</script>';
            $HTML[] = '<script type="application/ld+json">';
            $HTML[] = json_encode($jsonld_data,JSON_PRETTY_PRINT & JSON_UNESCAPED_SLASHES) ?? "{}";
            $HTML[] = '</script>';
            
            // Bottom of Head includes_html
            foreach ($includes_html['Head - End'] ?? [] as $val) {
                $HTML[] = $val;
            }

            $HTML[] = '</head>';
            $HTML[] = '<body>';

            // Top of body includes_html
            foreach ($includes_html['Body - Start'] ?? [] as $val) {
                $HTML[] = $val;
            }
            $HTML[] = $navigation["/$menuName"];



            unset($page_contents);
            $page_contents = (db_entry_exists($page, $conn)) ? db_get_contents($page, $conn) : "";
            $HTML[] = $page_contents;

            $HTML[] = $footer_file;

            // Bottom of body includes_html
            foreach ($includes_html['Body - End'] ?? [] as $val) {
                $HTML[] = $val;
            }

            $HTML[] = '</body>';
            $HTML[] = '</html>';

            foreach ($HTML as $key => $value) {
                $HTML[$key] = "$value\n";
            }


            $html_page_filename = $content_dirs[$page] . "/index.html";
            file_put_contents($html_page_filename, implode($HTML));

            $tmp .= str_replace(".html", "", basename($page)) . ", ";



            if (copy($html_page_filename, $content_dirs[$page] . "/" . basename($page))) {
                $tmp3 .= basename($page) . ", ";
            }


        }
        $logs[] = '[' . rtrim($tmp, ", ") . '] - Created Files';
        $logs[] = '[' . rtrim($tmp3, ": ") . '] - Copied and Verified Files ';
    }

    if ($go) {
        $landing_index =  "{$baseDir}$landingpagename/index.html";
        if (copy($landing_index,  "$baseDir/index.html")) {
            $logs[] = "[$landingpagename] - Created Landing Page";
            $logs[] = (memory_get_usage() - $startBytes) . " bytes";
        } else {
            $logs[] = "Landing Page Creation failed";
            $go = false;
        }
    }

    if ($go) {
        $logs[] = "Build has completed Successfully";
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

    return $logs;


}

function build_public()
{

    global $settings;

    $go = true;
    $logs[0] = false;
    $logs[] = "<b>Building for Deployment from File</b>";

    $logs[] = "Removing all created files - ignore [/app, /uploads]";
    $baseDir = $_SERVER['DOCUMENT_ROOT'];
    $files = listFilesInDirectory($baseDir);
    foreach ($files as $key => $file) {
        if (str_contains($file, "$baseDir/app")) {
            unset($files[$key]);
        }
        if (str_contains($file, "$baseDir/uploads")) {
            unset($files[$key]);
        }
    }
    $tmp = "[";
    foreach ($files as $key => $file) {
        $t = isset($t) ? $t + 1 : 1;
        $tmp .= ($t > 6) ? "\n" : basename($file) . ", ";
        $t = ($t > 6) ? 0 : $t;
        unlink($file);
    }

    $logs[] = rtrim($tmp, ", ") . "] - Deleted Files";
    $tmp = "[";
    $dirs = findEmptyDirs($baseDir);

    $logs[] = "Removing all created directories - ignore [/app, /uploads]";

    foreach ($dirs as $key => $dir) {
        if (str_contains($dir, "$baseDir/app")) {
            unset($dirs[$key]);
        }
        if (str_contains($dir, "$baseDir/uploads")) {
            unset($dirs[$key]);
        }

    }

    foreach ($dirs as $dir) {
        $t = isset($t) ? $t + 1 : 1;
        $tmp .= ($t > 6) ? "\n" : basename($dir) . ", ";
        $t = ($t > 6) ? 0 : $t;

        rmdir($dir);
    }

    $logs[] = rtrim($tmp, ", ") . "] - Deleted Directories";



    $logs[] = "Finding Index Options";
    $dir_settings = $settings->settings['out_dir'] . "/wmo/settings";
    $dir_content = $settings->settings['out_dir'] . "/wmo/content";


    $filename = "$dir_settings/includes.json";
    $script_filename = "$dir_settings/scripts.json";
    $action_filename = "$dir_settings/actions.json";
    $logs[] = $filename;

    if (file_exists($filename)) {
        $logs[] = "Index Options - <b>Found</b>";
    } else {
        $logs[] = "Index Options - <b>Not Found</b>";
        $go = false;
    }


    if ($go) {
        $logs[] = "<b>Finding Pages</b>";
        $pages_found = false;
        $tmp = "";
        foreach (glob("$dir_content/pages/*.html") as $files) {
            $tmp .= str_replace(".html", "", basename($files)) . ", ";
            $pages_found = true;
            $dir = $settings->settings['web_dir'] . "/" . str_replace(".html", "", basename($files));
            if (!is_dir($dir))
                mkdir($dir);
            $dirs[$files] = $dir;
        }
        $logs[] = '[' . rtrim($tmp, ", ") . '] - created Direcotries';
        $go = $pages_found;
    }

    if ($go) {
        $logs[] = "<b>Getting Fonts Css</b>";

        if (file_exists("$dir_content/css/fonts.css")) {
            if (!is_dir($settings->settings['web_dir'] . "/css")) {
                mkdir($settings->settings['web_dir'] . "/css");
                $logs[] = "[css] - created Directory";
            }
            if (copy("$dir_content/css/fonts.css", $settings->settings['web_dir'] . "/css/fonts.css")) {
                $logs[] = "[fonts.css]  - Copied";
            } else {
                $logs[] = "Fonts Css Failed";
            }
            $linkFonts = '<link rel="stylesheet" href="/css/fonts.css">';

        } else {
            $logs[] = "No fonts Css";
        }
    }

    if ($go) {
        $logs[] = "<b>Getting Palette Css</b>";
        if (file_exists("$dir_content/css/palette.css")) {
            if (!is_dir($settings->settings['web_dir'] . "/css")) {
                mkdir($settings->settings['web_dir'] . "/css");
                $logs[] = "[css] - created Directory";
            }
            if (copy("$dir_content/css/palette.css", $settings->settings['web_dir'] . "/css/palette.css")) {
                $logs[] = "[palette.css] - Copied";
            } else {
                $logs[] = "Palette Css Failed";

            }
            $linkPalette = '<link rel="stylesheet" href="/css/palette.css">';

        } else {
            $logs[] = "No fonts Css";
        }
    }

    if ($go) {
        $logs[] = "<b>Getting Custom Scripts</b>";
        $scripts = json_decode(file_get_contents($script_filename), true);
        if (!is_dir($settings->settings['web_dir'] . "/js")) {
            $logs[] = "[js] - created Directory";
            mkdir($settings->settings['web_dir'] . "/js");

        }
        $tmp = "";
        $tmp2 = "";
        foreach ($scripts as $key => $value) {
            if (($value['enabled'] == "true") ? true : false) {
                $html_tmp_scripts[$key] = '<script src="/js/' . $key . '"></script>';
                if (copy("$dir_content/scripts/$key", $settings->settings['web_dir'] . "/js/$key")) {
                    $tmp .= "$key, ";
                } else {
                    $logs[] = "Failed to find $key";
                    $go = false;
                }
            } else {
                $tmp2 .= "$key, ";
            }
        }
        $logs[] = '[' . rtrim($tmp, ", ") . '] - Created Files';
        $logs[] = '[' . rtrim($tmp2, ", ") . '] - Skipped Files';
    }

    if ($go) {
        $actions = json_decode(file_get_contents($action_filename), true);
        if (!is_dir($settings->settings['web_dir'] . "/js/action")) {
            $logs[] = "[js/action] - created Directory";
            mkdir($settings->settings['web_dir'] . "/js/action");
        }
        $tmp = "";
        $tmp2 = "";
        $logs[] = "<b>Getting Action Scripts</b>";
        foreach ($actions as $key => $value) {
            if (($value['enabled'] == "true") ? true : false) {
                if (copy("$dir_content/actions/$key", $settings->settings['web_dir'] . "/js/action/$key")) {
                    $tmp .= "$key, ";
                } else {
                    $logs[] = "Failed to find $key";
                    $go = false;
                }
            } else {
                $tmp2 .= "$key, ";
            }
        }
        $logs[] = '[' . rtrim($tmp, ", ") . '] - Created Files';
        $logs[] = '[' . rtrim($tmp2, ", ") . '] - Skipped Files';
    }


    if ($go) {
        $logs[] = "<b>Getting Header</b>";
        $nav_filename = "$dir_settings/navigation.json";
        $landing_filename = "$dir_settings/landing.json";

        $headerHTML = file_exists("$dir_content/header/header.html") ? file_get_contents("$dir_content/header/header.html") : "";
        $logs[] = "<b>Building Navigation</b>";
        $pages = [];
        $tmp = "";
        $menuArray = [];
        $navigationJSON = (file_exists($nav_filename)) ? json_decode(file_get_contents($nav_filename), true) : [];

        $landingPage = (file_exists($landing_filename)) ? json_decode(file_get_contents($landing_filename), true) : [];
        $lanpag = "/" . str_replace(".html", "", $landingPage['landing']);

        foreach ($navigationJSON as $value) {
            $navigationArray[] = $value['navurl'];

            $menuArray[$value['navtext']] = $value['navurl'];
        }

        $tmp = "";

        foreach (glob("$dir_content/pages/*.html") as $file) {
            $folder = "/" . str_replace(".html", "", basename($file));
            $tmp .= $folder . ", ";
            //if (in_array($folder, $navigationArray)) {
            $doc = new DOMDocument('1.0', "UTF-8");
            libxml_use_internal_errors(true); // Suppress warnings due to malformed HTML
            $doc->loadHTML('<header id="realheader">' . $headerHTML . "</header>");
            libxml_clear_errors();
            $templateLiActive = $doc->getElementById('templateActive');
            $templateLi = $doc->getElementById('template');
            $menuItemsUl = $doc->getElementById('menuItems');

            $header = $doc->getElementById("realheader");
            /*
            $styles = $doc->getElementsByTagName("style");
            foreach ($styles as $node) {
                $doc->parentNode->removeChild($node); // Remove the node from its parent
            }
*/
            foreach ($menuArray as $title => $href) {
                $tmp2 = ($href == "/") ? $lanpag : $href;
                $newLi = ($folder == $tmp2) ? $templateLiActive->cloneNode(true) : $templateLi->cloneNode(true);
                $link = $newLi->getElementsByTagName('a')->item(0);
                $link->setAttribute('href', $href);
                $link->textContent = $title;
                $menuItemsUl->appendChild($newLi);
            }
            $menuItemsUl->removeChild($templateLiActive);
            $menuItemsUl->removeChild($templateLi);

            //$navigation[$folder] = $doc->saveHTML($header);
            $navigation[$folder] = (function ($header, $dom) {
                $innerHtml = "";
                foreach ($header->childNodes as $child) {
                    $innerHtml .= $dom->saveHTML($child);
                }
                return $innerHtml;
            })($header, $doc);
            //}
        }
        $logs[] = '[' . rtrim($tmp, ", ") . '] - Navigation Built';
    }

    if ($go) {
        $logs[] = "<b>Getting Footer</b>";
        $footerHTML = file_exists("$dir_content/footer/footer.html") ? file_get_contents("$dir_content/footer/footer.html") : "";
    }

    if ($go) {
        $includes = json_decode(file_get_contents($filename), true);
        foreach ($includes as $key => $value) {
            switch ($value['includes_type']) {
                case "Stylesheet":
                    $html_tmp[$value['includes_location']][] = '<link rel="stylesheet" href="' . $value['includes_url'] . '" />';
                    break;
                case "Script":
                    $html_tmp[$value['includes_location']][] = '<script src="' . $value['includes_url'] . '"></script>';
                    break;
                case "HTML":
                    $html_tmp[$value['includes_location']][] = $value['includes_url'];
                    break;
            }
        }
    }

    if ($go) {
        $faviconDetails = (file_exists("$dir_settings/favicon.json")) ? json_decode(file_get_contents("$dir_settings/favicon.json"), true) : [];
        if (isset($faviconDetails['favicon']) && file_exists("$baseDir/uploads/{$faviconDetails['favicon']}")) {

            if (copy("$baseDir/uploads/{$faviconDetails['favicon']}", "{$baseDir}/favicon.ico")) {
                $logs[] = "<b>Favicon copied successfully</b>";
            } else {
                $logs[] = "Failed to copy favicon";
            }
        }
    }

    if ($go) {
        $logs[] = "<b>Getting SEO Settings</b>";
        $seofilename = "$dir_settings/seo.json";
        $seo = (file_exists($seofilename)) ? json_decode(file_get_contents($seofilename), true) : [];

        $logs[] = "<b>Building Html Files</b>";
        $tmp = '';
        $tmp3 = '';
        foreach (glob("$dir_content/pages/*.html") as $files) {
            $menuName = str_replace(".html", "", basename($files));

            $HTML = [];
            $HTML[] = '<!DOCTYPE html>';
            $HTML[] = '<html lang="en">';
            $HTML[] = '';
            $HTML[] = '<head>';
            $seotag = "title";
            $HTML[] = (isset($seo[$menuName][$seotag]) && !empty(isset($seo[$menuName][$seotag]))) ? '<title>' . $seo[$menuName]['title'] . '</title>' : "<title>NO TITLE</title>";
            $seotag = "faviconUrl";
            $HTML[] = (isset($seo[$menuName][$seotag]) && !empty(isset($seo[$menuName][$seotag]))) ? '<link rel="icon" href="' . $seo[$menuName]['faviconUrl'] . '" type="image/x-icon">' : "";
            $seotag = "robotsMeta";
            $HTML[] = (isset($seo[$menuName][$seotag]) && !empty(isset($seo[$menuName][$seotag]))) ? '<meta name="robots" content="' . $seo[$menuName]['robotsMeta'] . '">' : "";
            $seotag = "metaDescription";
            $HTML[] = (isset($seo[$menuName][$seotag]) && !empty(isset($seo[$menuName][$seotag]))) ? '<meta name="description" content="' . $seo[$menuName]['metaDescription'] . '">' : "";
            $seotag = "canonicalUrl";
            $HTML[] = (isset($seo[$menuName][$seotag]) && !empty(isset($seo[$menuName][$seotag]))) ? '<meta rel="canonical" href="' . $seo[$menuName]['canonicalUrl'] . '">' : "";
            $seotag = "ogTitle";
            $HTML[] = (isset($seo[$menuName][$seotag]) && !empty(isset($seo[$menuName][$seotag]))) ? '<meta property="og:title" content="' . $seo[$menuName]['ogTitle'] . '" />' : "";
            $seotag = "ogDescription";
            $HTML[] = (isset($seo[$menuName][$seotag]) && !empty(isset($seo[$menuName][$seotag]))) ? '<meta property="og:desctiption" content="' . $seo[$menuName]['ogDescription'] . '" />' : "";
            $seotag = "canonicalUrl";
            $HTML[] = (isset($seo[$menuName][$seotag]) && !empty(isset($seo[$menuName][$seotag]))) ? '<meta property="og:url" content="' . $seo[$menuName]['canonicalUrl'] . '" />' : "";
            $seotag = "ogImage";
            $HTML[] = (isset($seo[$menuName][$seotag]) && !empty(isset($seo[$menuName][$seotag]))) ? '<meta property="og:image" content="' . $seo[$menuName]['ogImage'] . '" />' : "";

            foreach ($html_tmp['Head - Start'] ?? [] as $val) {
                $HTML[] = $val;
            }

            foreach ($html_tmp['Head - End'] ?? [] as $val) {
                $HTML[] = $val;
            }

            $HTML[] = $linkFonts;
            $HTML[] = $linkPalette;
            foreach ($html_tmp_scripts ?? [] as $val) {
                $HTML[] = $val;
            }
            $HTML[] = '<script type="application/ld+json">';
            $HTML[] = $seo[$menuName]['schemaMarkup'] ?? "{}";
            $HTML[] = '</script>';

            $HTML[] = '</head>';
            $HTML[] = '<body>';
            foreach ($html_tmp['Body - Start'] ?? [] as $val) {
                $HTML[] = $val;
            }
            $HTML[] = $navigation["/$menuName"];

            $file_contents = file_get_contents($files);
            $HTML[] = $file_contents;
            $HTML[] = $footerHTML;

            foreach ($html_tmp['Body - End'] ?? [] as $val) {
                $HTML[] = $val;
            }

            $HTML[] = '</body>';
            $HTML[] = '</html>';

            foreach ($HTML as $key => $value) {
                $HTML[$key] = "$value\n";
            }


            $filelocation = $dirs[$files] . "/index.html";
            file_put_contents($filelocation, implode($HTML));
            $tmp .= str_replace(".html", "", basename($files)) . ", ";



            if (copy($files, $dirs[$files] . "/" . basename($files))) {
                $tmp3 .= basename($files) . "-OK: ";
            } else {
                $tmp3 .= basename($files) . "-FAIL: ";
            }
            ;

        }
        $logs[] = '[' . rtrim($tmp, ", ") . '] - Created Files';
        $logs[] = '[' . rtrim($tmp3, ": ") . '] - Copied and Verified Files ';
    }


    if ($go) {

        if (file_exists("$dir_settings/landing.json")) {
            $landingpage = json_decode(file_get_contents("$dir_settings/landing.json"), true);
            $landing = $landingpage['landing'];
            $logs[] = "<b>Finding Landing Page </b>";
        } else {
            $logs[] = "No Landing Page has been set";
            $go = false;
        }
    }

    if ($go) {
        $landingindex = $settings->settings['web_dir'] . "/" . str_replace(".html", "", $landing) . "/index.html";

        if (copy($landingindex, $settings->settings['web_dir'] . "/index.html")) {
            $logs[] = "[$landing] - Created File";

        } else {
            $logs[] = "Landing Page Creation failed";
            $go = false;
        }
    }

    foreach ($logs as $key => $value) {
        if (is_string($value)) {
            $logs[$key] = "$value\n";
        }
    }

    return $logs;
}
function build_construction()
{
    global $settings;



    $dir_content = $settings->settings['out_dir'] . "/wmo/content";

    $logs[0] = false;

    $baseDir = $_SERVER['DOCUMENT_ROOT'];

    $files = listFilesInDirectory($baseDir);
    foreach ($files as $key => $file) {
        if (str_contains($file, "$baseDir/app")) {
            unset($files[$key]);
        }
        if (str_contains($file, "$baseDir/uploads")) {
            unset($files[$key]);
        }

    }
    $tmp = "[";
    foreach ($files as $key => $file) {
        $t = isset($t) ? $t + 1 : 1;
        $tmp .= ($t > 6) ? "\n" : basename($file) . ", ";
        $t = ($t > 6) ? 0 : $t;
        unlink($file);
    }

    $logs[] = rtrim($tmp, ", ") . "] - Deleted Files";
    $tmp = "[";
    $dirs = findEmptyDirs($baseDir);

    $logs[] = "Removing all created directories - ignore [/app, /uploads]";

    foreach ($dirs as $key => $dir) {
        if (str_contains($dir, "$baseDir/app")) {
            unset($dirs[$key]);
        }
        if (str_contains($dir, "$baseDir/uploads")) {
            unset($dirs[$key]);
        }

    }

    foreach ($dirs as $dir) {
        $t = isset($t) ? $t + 1 : 1;
        $tmp .= ($t > 6) ? "\n" : basename($dir) . ", ";
        $t = ($t > 6) ? 0 : $t;

        rmdir($dir);
    }

    $logs[] = rtrim($tmp, ", ") . "] - Deleted Directories";

    $logs[] = "Build Construction page selected";
    $logs[] = "Finding Under-Construction Template";


    $selectedTemplate = $_POST['uctemplate'];
    $expected_uc = "$dir_content/$selectedTemplate.html";
    $logs[] = $expected_uc;

    $go = true;

    if (file_exists($expected_uc)) {
        $logs[] = "Under-construction Template - <b>Found</b>";
    } else {
        $logs[] = "Under-construction Template - <b>Not Found</b>";
        $logs[] = "<b>Please select a valid template</b>";
        $logs[] = "Exiting Build";
        $logs[0] = false;
        $go = false;
    }

    if ($go) {
        $logs[] = "Finding Website Root";
        $index = $settings->settings['web_dir'] . "/index.html";
        $logs[] = $index;
        if (file_exists($index)) {
            $logs[] = "Existing Index Found";
            if (unlink($index)) {
                $logs[] = "Existing Index Deleted";
            } else {
                $logs[] = "Unable to delete existing index";
                $logs[] = "Check permissions for the file";
                $go = false;
            }
        } else {
            $logs[] = "Existing Index not found";
        }
        if ($go) {
            if (copy($expected_uc, $index)) {
                $logs[] = "Web root index file has been created from <i>$selectedTemplate</i>";
            } else {
                $logs[] = "Unable to create new index file";
                $logs[] = "Check permissions for the directory";
                $go = false;
            }
        }
    }

    if ($go) {
        $logs[] = "<b>Build Completed Successfully</b>";
        $logs[0] = true;
    }

    foreach ($logs as $key => $value) {
        if (is_string($value)) {
            $logs[$key] = "$value\n";
        }
    }
    return $logs;
}
function question($question)
{
    global $settings;
    $dir_settings = $settings->settings['out_dir'] . "/wmo/settings/";
    $filename = "$dir_settings/apikeys.json";
    $apis = (file_exists($filename)) ? json_decode(file_get_contents($filename), true) : [];

    $apiKey = $apis['gemini_ai'] ?? null;
    if (isset($apiKey)) {

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=" . $apiKey;

        $data = [
            "contents" => [
                [
                    "parts" => [
                        [
                            "text" => $question
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