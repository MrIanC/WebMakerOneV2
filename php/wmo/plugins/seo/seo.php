<?php

$dir_content_pages = $settings->settings['out_dir'] . "/wmo/content/pages";
$dir_content = $settings->settings['out_dir'] . "/wmo/content";
$dir_settings = $settings->settings['out_dir'] . "/wmo/settings";
$seofilename = "$dir_settings/seo.json";
$datafilename = "$dir_settings/data.json";
$faviconSettingsFilename = "$dir_settings/favicon.json";

$AllHeadings = [];
$page_list = "";

if (($useDB ?? "no") == "yes") {
    $data = (db_entry_exists($datafilename, $conn)) ? json_decode(db_get_contents($datafilename, $conn), true) : [];
    $faviconDetails = (db_entry_exists($faviconSettingsFilename, $conn)) ? json_decode(db_get_contents($faviconSettingsFilename, $conn), true) : [];
    $seo = (db_entry_exists($seofilename, $conn)) ? json_decode(db_get_contents($seofilename, $conn), true) : [];

    foreach (db_glob("$dir_content_pages/*.html", $conn) as $files) {
        $page_name = str_replace(".html", "", basename($files));
        $filename = basename($files);
        $filedetails[$page_name] = date("Y-m-d\TH:i:sP", strtotime(db_timestamp($files, $conn)));

        $page_list .= str_replace(
            ['#pagename#', '#filename#'],
            [$page_name, $filename],
            '
            <div>
            <button class="btn btn-link" name="content" value="#pagename#">#pagename#</button>
            </div>
            '
        );
    }
} else {
    $data = (file_exists($datafilename)) ? json_decode(file_get_contents($datafilename), true) : [];
    $faviconDetails = (file_exists($faviconSettingsFilename)) ? json_decode(file_get_contents($faviconSettingsFilename), true) : [];
    $seo = (file_exists($seofilename)) ? json_decode(file_get_contents($seofilename), true) : [];
    foreach (glob("$dir_content_pages/*.html") as $files) {
        $page_name = str_replace(".html", "", basename($files));
        $filename = basename($files);
        $filedetails[$page_name] = date("Y-m-d\TH:i:sP", filemtime($files));
        $page_list .= str_replace(
            ['#pagename#', '#filename#'],
            [$page_name, $filename],
            '
            <div>
            <button class="btn btn-link" name="content" value="#pagename#">#pagename#</button>
            </div>
            '
        );
    }
}

$url = $data['url'] ?? "https://example.com";
$current = $_GET['content'] ?? null;

foreach ($seo as $key => $value) {
    if (!isset($filedetails[$key])) {
        unset($seo[$key]);
    }
}

//file_put_contents($seofilename, json_encode($seo, JSON_PRETTY_PRINT));

if (isset($current)) {
    if (isset($_POST['title'])) {
        $seo[$current] = $_POST;
        if (($useDB ?? "no") == "yes") {
            db_put_contents($seofilename, json_encode($seo, JSON_PRETTY_PRINT), $conn);
        } else {
            file_put_contents($seofilename, json_encode($seo, JSON_PRETTY_PRINT));
        }


        /*
        $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header("Location: $fullUri");
        */
    }

    $doc = new DOMDocument();
    libxml_use_internal_errors(true); // Suppress warnings for invalid HTML
    $current_filename = "$dir_content_pages/$current.html";
    if (($useDB ?? "no") == "yes") {
        $html_content = (db_entry_exists($current_filename, $conn)) ? db_get_contents($current_filename, $conn) : [];
    } else {
        $html_content = file_get_contents($current_filename);
    }

    $html_content = empty($html_content) ? "<div></div>" : $html_content;
    $doc->loadHTML($html_content);

    $doc->getElementsByTagName("h1");

    $title = $doc->getElementsByTagName("h1")->item(0)->textContent ?? "NO HEADING1";
    $seo[$current]['heading1'] = $title;
    $seo[$current]['title'] ??= $title;


    $metaDescription = (function ($dom) {
        $headings = "";
        foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $headingLevel) {
            foreach ($dom->getElementsByTagName($headingLevel) as $key => $value) {
                $headings .= str_replace(["\n", "\r", "  "], "", ($value->textContent ?? "")) . ", ";
            }
        }
        return ($headings == "") ? null : $headings;
    })($doc) ?? "NO HEADINGS TO MAKE DESCRIPTION";

    $seo[$current]['headings'] = $metaDescription ?? "";
    $seo[$current]['metaDescription'] ??= $metaDescription;


    $pageUrl = "$url/$current";

    $seo[$current]['canonicalUrl'] ??= $pageUrl;
    $seo[$current]['RealUrl'] = $pageUrl;


    $seo[$current]['ogTitle'] ??= $seo[$current]['title'];
    $seo[$current]['ogDescription'] ??= $seo[$current]['metaDescription'];

    $images = $doc->getElementsByTagName("img");

    $imageUrl = ($images->length > 0) ? $images->item(0)->getAttribute('src') : "NO IMAGE FOUND";

    $seo[$current]['ogImage'] ??= $imageUrl;


    $seo[$current]['faviconUrl'] = "/" . $faviconDetails['favicon'] ??= "NO FAVICON SET";
    $seo[$current]['priority'] ??= "0.8";

    $src = [];
    $rep = [];

    $body = $doc->getElementsByTagName("section");
    if ($body) {
        foreach ($body as $childe) {
            $id = ($childe->getAttribute('id') ?? null);
            if (isset($id)) {
                $tmp = new DOMDocument();
                $tmp->loadHTML($doc->saveHTML($childe));

                foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $hval) {
                    $headings = $tmp->getElementsByTagName($hval);
                    if ($headings) {
                        foreach ($headings as $childHead) {
                            $AllHeadings[$id][] = preg_replace('/\s+/', ' ', ($childHead->textContent ?? "No Heading"));
                        }
                    }
                }
            }
        }
    } else {
        echo "nobody";
    }

    //Seperated from SEO Moved to Publish
    /*$JSONLD = [
        [
            "@context" => "https://schema.org",
            "@type" => "LocalBusiness",
            "url" => $url,
            "logo" => $data['logo'],
            "image" => $data['imageurl'],
            "name" => $data['name'],
            "description" => $data['description'],
            "email" => $data['email'],
            "telephone" => $data['telephone'],
            "address" => [
                "@type" => "PostalAddress",
                "streetAddress" => $data['streetAddress'],
                "addressLocality" => $data['city'],
                "addressRegion" => $data['province'],
                "postalCode" => $data['postalCode'],
                "addressCountry" => $data['country']
            ],
            "openingHours" => isset($data['openingHours']) ? (function ($data) {
                foreach ($data as $day => $hours) {
                    $dayOfWeek[] = [
                        "@type" => "OpeningHoursSpecification",
                        "dayOfWeek" => $day,
                        "opens" => $hours[0] ?? "8am",
                        "closes" => $hours[1] ?? "5pm"
                    ];
                }
                return $dayOfWeek;
            })($data['openingHours']) : [],
            "sameAs" => $data['socialMedia'] ?? [],
            "priceRange" => $data['priceRange'] ?? "R0 - R0",
        ]
    ];*/
    foreach ($AllHeadings as $key => $value) {

        $JSONLD[] = [
            "@context" => "https://schema.org",
            "@type" => "BreadcrumbList",
            "name" => $value[0],
            "itemListElement" => (function ($arr, $key, $url) {
                $pos = 0;
                foreach ($arr as $k => $v) {
                    $pos++;
                    $tmp[] = [
                        '@type' => "ListItem",
                        'position' => $pos,
                        'name' => $v,
                        'item' => $url . "/#$key"
                    ];
                }
                return $tmp;
            })($value, $key, $url)
        ];

    }

    $seo[$current]['schemaMarkup'] ??= "";
    if ($seo[$current]['schemaMarkup'] == "") {
        $seo[$current]['schemaMarkup'] = null;
    }

    if (!isset($seo[$current]['schemaMarkup'])) {
        $seo[$current]['schemaMarkup'] = json_encode($JSONLD, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES);
    } else {
        //print_r(json_decode($seo[$current]['schemaMarkup'],true));
    }

    $seo[$current]['schemaMarkup'] ??= "";

    foreach ($seo[$current] ?? [] as $key => $value) {
        $src[] = "#qna_$key#";
        $rep[] = $value;
    }

    $page_seo = str_replace(
        $src,
        $rep,
        file_get_contents(__DIR__ . "/qna.html")
    );
} else {
    $sitemap[] = '<?xml version="1.0" encoding="UTF-8"?>';
    $sitemap[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    $sitemapForm = "";
    $robots = [];
    $robots[] = "User-agent: *";
    ;
    foreach ($seo as $key => $value) {
        $title = $value['title'] ?? "NO TITLE";
        $metaDescription = $value['metaDescription'] ?? "NO DESCRIPTION";
        $lasmod = $filedetails[$key] ?? "";
        $changefreq = $value['changeFreq'] ?? "monthly";
        $priority = $value['priority'] ?? "0.8";
        $url = $value['canonicalUrl'] ?? "NO URL SET!";
        $nofollow = $value['robotsMeta'] ?? "no";
        $page_local = str_replace($data['url'], "", $url);
        if (str_contains($nofollow, "no")) {
            $robots[] = "Disallow: $page_local";
        } else {
            $robots[] = "Allow: $page_local";
        }

        if (!str_contains($nofollow, "no")) {


            $sitemap[] = str_replace(
                ["#title#", '#description#', "#lastmod#", "#changefreq#", "#url#", "#priority#"],
                [$title, $metaDescription, $lasmod, $changefreq, $url, $priority],
                '<url>' . "\n" .
                '  <loc>#url#</loc>' . "\n" .
                '  <lastmod>#lastmod#</lastmod>' . "\n" .
                '  <changefreq>#changefreq#</changefreq>' . "\n" .
                '  <priority>#priority#</priority>' . "\n" .
                '</url>' . "\n"
            );


            $sitemapForm .= str_replace(
                ["#title#", '#description#', "#lastmod#", "#changefreq#", "#url#", "#priority#"],
                [$title, $metaDescription, $lasmod, $changefreq, $url, $priority],
                '
            <div class="col-12">
                <div class="border-bottom mb-4 p-3">
                    <div>
                        <div>
                            <strong>URL</strong> <span>#url#</span>
                        </div>
                        <div>
                            <strong>Title</strong> <span>#title#</span>
                        </div>
                        <div>
                            <strong>Last Modified</strong> <span>#lastmod#</span>
                        </div>
                        <div>
                            <strong>Change Frequency</strong> <span>#changefreq#</span>
                        </div>
                        <div>
                            <strong>Priority</strong> <span>#priority#</span>
                        </div>
                        <div class="small">
                        #description#
                        </div>
                    </div>
                        
                </div>
            </div>
            '
            );
        }
    }
    $sitemap[] = '</urlset>';

    $sitemapFilename = "$dir_content/sitemap.xml";
    $robotsFilename = "$dir_content/robots.txt";
    if (($useDB ?? "no") == "yes") {
        db_put_contents($sitemapFilename, implode("\n", $sitemap), $conn);
        db_put_contents($robotsFilename, implode("\n", $robots), $conn);
    } else {
        file_put_contents($sitemapFilename, implode("\n", $sitemap));
        file_put_contents($robotsFilename, implode("\n", $robots));
    }


    ;
    //print_r($sitemap);
    $page_seo = str_replace(
        ['#sitemap#'],
        [$sitemapForm],
        file_get_contents(__DIR__ . "/sitemap.html")
    );

}



$html_body = str_replace(
    ['#page-list#', '#page-seo#'],
    [$page_list, $page_seo],
    file_get_contents(__DIR__ . "/form.html")
);
