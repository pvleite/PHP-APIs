<?php
ini_set('display_errors', true); // debugging

require_once("./example/api.extended.php");

$api = new Example();

include "./include/header.php";

include $api->validPath();

include "./include/footer.php";