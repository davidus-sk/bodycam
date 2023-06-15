<?php

include 'bootstrap.php';

$headers = getallheaders();
$headers = array_map(function($val) { return is_array($val) && count($val)===1 ? $val[0] : $val; }, $headers);
$headers = array_combine(array_keys($headers), array_values($headers));

echo jsonResponse([
    'method' => $_SERVER['REQUEST_METHOD'],
    'time' => date(DATE_ATOM),
    "headers" => $headers,
]);