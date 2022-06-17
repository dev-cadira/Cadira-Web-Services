<?php

ini_set('display_errors', 'on');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);

require_once 'vendor/autoload.php';
require_once 'WSCadira.php';

use Cadira\Cadira;

//Create Object
$cadiraObj = new Cadira('VTIGER_URL', 'VTIGER_USER', 'VTIGER_ACCESSKEY');
if($cadiraObj){
    $result = $cadiraObj->listTypes();
    $json_string = json_encode($result, JSON_PRETTY_PRINT);
    echo "<pre>".$json_string."<pre>";    
}

