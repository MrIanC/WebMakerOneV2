<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Report all PHP errors
error_reporting(E_ALL);

include "settings.php";


//print_r($settings);
echo $htmlDOC->htmlString();
