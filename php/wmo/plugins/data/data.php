<?php

$dir_settings = $settings->settings['out_dir'] . "/wmo/settings";
$filename = "$dir_settings/data.json";
$jsonld_data_filename = "$dir_settings/jsonld.json";

if (($useDB ?? "no") == "yes") {
    $data = (db_entry_exists($filename, $conn)) ? json_decode(db_get_contents($filename, $conn), true) : [];
    $jsonld_data = (db_entry_exists($jsonld_data_filename, $conn)) ? json_decode(db_get_contents($jsonld_data_filename, $conn), true) : [];
} else {
    $data = (file_exists($filename)) ? json_decode(file_get_contents($filename), true) : [];
    $jsonld_data = (file_exists($jsonld_data_filename)) ? json_decode(file_get_contents($jsonld_data_filename), true) : [];
}

$inputArray = ['name', 'description', 'imageurl', 'logo', 'telephone', 'email', 'url', 'streetAddress', 'city', 'province', 'postalCode', 'country', 'openingHours', 'socialMedia', "priceRange"];

if (empty($data['openingHours'])) {
    $data['openingHours'] = [
        [
            "monday" => ["8am", "9am"],
            "tuesday" => ["8am", "9am"],
            "wednesday" => ["8am", "9am"],
            "thursday" => ["8am", "9am"],
            "friday" => ["8am", "9am"],
            "saturday" => ["8am", "9am"],
            "sunday" => ["8am", "9am"],
        ]
    ];
}

foreach ($inputArray as $input) {
    $temp = isset($data[$input]) ? (is_array($data[$input]) ? json_encode($data[$input], JSON_PRETTY_PRINT & JSON_UNESCAPED_SLASHES) : $data[$input]) : "";
    $_POST[$input] ??= $temp;

}


$inputs = [];
foreach ($_POST as $key => $value) {
    $rows = 1;
    if (isValidJSON($value)) {
        if (is_array(json_decode($value))) {
            $rows = 12;

            $_POST[$key] = json_decode($value, true);
        }
    } else {
        if (strlen($value) > 40) {
            $rows = 3;
        }
        if (strlen($value) > 120) {
            $rows = 6;
        }
        if (strlen($value) > 600) {
            $rows = 12;
        }
    }
    $inputs[] = '
        <div class="mb-3">
        <label for="' . $key . '">' . ucwords($key) . '</label>    
        <textarea type="text" class="form-control" name="' . $key . '" rows="' . $rows . '">' . $value . '</textarea>
            
        </div>
';

}

$data = $_POST;
//Seperated from SEO Moved to data
$JSONLD = [
    [
        "@context" => "https://schema.org",
        "@type" => "LocalBusiness",
        "url" => $data['url'],
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
            $dayOfWeek = [];
            if (!is_array($data))
                return null;
            foreach ($data as $day => $hours) {
                $dayOfWeek[] = [
                    "@type" => "OpeningHoursSpecification",
                    "dayOfWeek" => $day,
                    "opens" => $hours[0] ?? "8am",
                    "closes" => $hours[1] ?? "5pm"
                ];
            }
            return $dayOfWeek;
        })($data['openingHours'][0]??[]) : [],
        "sameAs" => $data['socialMedia'] ?? [],
        "priceRange" => $data['priceRange'] ?? "R0 - R0",
    ]
];

if (($useDB ?? "no") == "yes") {
    db_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT & JSON_UNESCAPED_SLASHES), $conn);
    db_put_contents($jsonld_data_filename, json_encode($JSONLD, JSON_PRETTY_PRINT & JSON_UNESCAPED_SLASHES), $conn);
} else {
    file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
    file_put_contents($jsonld_data_filename, json_encode($JSONLD, JSON_PRETTY_PRINT));
}


$html_body = str_replace(
    ['#form#'],
    [implode($inputs)],
    file_get_contents(__DIR__ . "/form.html")
);