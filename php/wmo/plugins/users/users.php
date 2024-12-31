<?php
$dir_settings = $settings->settings['out_dir'] . "/wmo/settings";
$filename = "$dir_settings/users.json";


if (($useDB ?? "no") == "yes") {
    $users = (db_entry_exists($filename, $conn)) ? json_decode(db_get_contents($filename, $conn), true) : [];
} else {
    $users = (file_exists($filename)) ? json_decode(file_get_contents($filename), true) : [];
}

if (isset($_POST['delete'])) {
    $username = $_POST['delete'];
    unset($users[$username]);
    
    if (($useDB ?? "no") == "yes") {
        db_put_contents($filename, json_encode($users, JSON_PRETTY_PRINT), $conn);
    } else {
        file_put_contents($filename, json_encode($users, JSON_PRETTY_PRINT));
    }



    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");

}

if (isset($_POST['username'], $_POST['password'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $users[$username] = ['username' => $username, 'password' => $password];
    
    if (($useDB ?? "no") == "yes") {
        db_put_contents($filename, json_encode($users, JSON_PRETTY_PRINT), $conn);
    } else {
        file_put_contents($filename, json_encode($users, JSON_PRETTY_PRINT));
    }

    //file_put_contents($filename, json_encode($users, JSON_PRETTY_PRINT));
    //db_put_contents($filename,json_encode($users, JSON_PRETTY_PRINT),$conn);

    $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $fullUri");

}

$currentUser = json_decode(base64_decode($_SESSION['authtoken']), true)['username'] ?? "";

$userlist = "";
foreach ($users as $key => $user) {
    $disabled = '';

    if ($currentUser === $user['username']) {
        $disabled = 'disabled="disabled"';
    }

    $userlist .= str_replace(
        ["#username#", "#disabled#"],
        [$user['username'], $disabled],
        '<div class="d-flex justify-content-between align-items-center border-bottom">
    <div>
        <div class="d-flex justify-content-between align-items-center">
            <i class="fs-3 p-2 bi #landing#"></i>
            <div>
                <div>
                    #username#
                </div>
            </div>
        </div>
    </div>
    <div>
        <button class="btn btn-primary btn-sm" name="delete" value="#username#" #disabled#><i class="bi bi-x-square"></i></button>
    </div>
</div>'
    );
}

$html_body = str_replace(
    ["#user-list#"],
    [$userlist],
    file_get_contents(__DIR__ . "/form.html")
);