<?php

define('ROOT_DIR', __DIR__);
//define('CLASS_DIR', __DIR__ . '/classes');

include_once 'functions.php';
include_once 'Database.php';

// components
$Config = include 'config.php';
$Db = null;

$dbHost = isset($Config['db.host']) ? $Config['db.host'] : null;
$dbUsername = isset($Config['db.username']) ? $Config['db.username'] : null;
$dbPassword = isset($Config['db.password']) ? $Config['db.password'] : null;
$dbName = isset($Config['db.name']) ? $Config['db.name'] : null;
$dsn = sprintf('mysql:host=%s;dbname=%s;charset=UTF8', $dbHost, $dbName);

// connect to the database
try {

	$db = new Database($dbName, $dbUsername, $dbPassword, $dbHost);

} catch(\PDOException $e) {

	echo "Connection failed: " . $e->getMessage();

}