<?php

function isValidJSON($string)
{
    json_decode($string);
    return (json_last_error() === JSON_ERROR_NONE);
}


$dir_settings = $settings->settings['out_dir'] . "/wmo/settings";
$filename = "$dir_settings/users.json";

if (($useDB ?? "no") == "yes") {
    $users = (db_entry_exists($filename, $conn)) ? json_decode(db_get_contents($filename, $conn), true) : [];
} else {
    $users = (file_exists($filename)) ? json_decode(file_get_contents($filename), true) : [];
}



if (!empty($_POST) && isset($_POST['login'])) {
    $_POST['login'] ??= "na";
    if ($_POST['login'] === "out") {
        echo "Logout";
        unset($_SESSION['authtoken']);
        $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header("Location: $fullUri");
    }
}

if (!empty($_POST) && isset($_POST['username'], $_POST['password'], $_POST['login'])) {

    if (empty($users)) {
        $dir_settings = $settings->settings['out_dir'] . "/wmo/settings";

        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $filename = "$dir_settings/users.json";
        $users[$username] = ['username' => $username, 'password' => $password];
        if (($useDB ?? "no") == "yes") {
            db_put_contents($filename, json_encode($users, JSON_PRETTY_PRINT), $conn);
        } else {
            file_put_contents($filename, json_encode($users, JSON_PRETTY_PRINT));
        }
    }



    $authenicationKeyPrep = $_POST['username'] . date("Ymdh");

    $authUser = $users[$_POST['username']] ?? null;

    print_r($authUser);

    if (isset($authUser['username'], $authUser['password'])) {
        if (password_verify($_POST['password'], $authUser['password'])) {
            $_SESSION['authtoken'] = base64_encode(json_encode([
                "username" => $_POST['username'],
                "iat" => time(),
                "exp" => time() + 3600
            ]));

            $fullUri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("Location: $fullUri");

        } else {
            $_SESSION['authtoken'] = false;
        }
    } else {
        $_SESSION['authtoken'] = false;
    }

    //check password then do this:

}

if (!(($_SESSION['authtoken'] ?? false) === false)) {
    $base64 = base64_decode($_SESSION['authtoken']);
    if (isValidJSON($base64)) {
        $token = json_decode($base64, true);
        if ($token['exp'] < time()) {
            unset($_SESSION['authtoken']);

        }
    } else {
        unset($_SESSION['authtoken']);
    }

}




if (($_SESSION['authtoken'] ?? false) === false) {
    $loginpage = new HTML_Structure();
    $loginpage->inject("head", '<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>', "end");
    $loginpage->inject("body", '<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js" integrity="sha512-ykZ1QQr0Jy/4ZkvKuqWn4iF3lqPZyij9iRv6sGqLRdTPkY69YX6+7wvVGmsdBbiIfN/8OdsI7HABjvEok6ZopQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>', "end");
    $loginpage->inject("head", '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" integrity="sha512-jnSuA4Ss2PkkikSOLtYs8BlYIeeIK1h99ty4YfvRPAlzr377vr3CXDb7sb7eEEBYjDtcYj+AjBH3FLv5uSJuXg==" crossorigin="anonymous" referrerpolicy="no-referrer" />', "end");
    $loginpage->inject("head", '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />', "end");

    if (empty($users)) {
        $loginpage->inject('body', file_get_contents(__DIR__ . "/new_user.html"));
    } else {
        $loginpage->inject('body', file_get_contents(__DIR__ . "/login_page.html"));
    }
    echo $loginpage->doc->saveHTML();
    exit;
}

