<?php
header("Content-type:application/json");
error_reporting(0);
date_default_timezone_set('UTC');
require_once("classes/lpstore.php");
$lpStore = new LPStore($_GET["corpid"]);
echo json_encode($lpStore->get(), JSON_PRETTY_PRINT);