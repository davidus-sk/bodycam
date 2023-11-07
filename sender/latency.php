#!/usr/bin/php
<?php

// script
///////////////////////////////////////////////////////////////////////////////

set_time_limit(0);

// common functions
///////////////////////////////////////////////////////////////////////////////
include __DIR__ . '/../lib/functions.php';

// dont run more than once
///////////////////////////////////////////////////////////////////////////////

// we dont want this to run twice
$lockFile = fopen('/tmp/latency.pid', 'c');
$gotLock = flock($lockFile, LOCK_EX | LOCK_NB, $wouldBlock);
if ($lockFile === false || (!$gotLock && !$wouldBlock)) {
	throw new Exception(date('r') . "> Can't obtain lock.");
} else if (!$gotLock && $wouldBlock) {
	echo date('r') . "> Another instance is already running; terminating.\n";
	exit;
}//if

ftruncate($lockFile, 0);
fwrite($lockFile, getmypid() . "\n");

// perform latency test
///////////////////////////////////////////////////////////////////////////////

$receiver_ip = get_receiver_ip('10.220.0.1');

if (!empty($receiver_ip)) {
	$ping = trim(`/usr/bin/ping $receiver_ip -c 10 -q`);

	if (preg_match("@\s+[0-9.]+/([0-9.]+)/@", $ping, $m)) {
		file_put_contents('/tmp/latency.dat', $m[1]);
	}//if
}//if
