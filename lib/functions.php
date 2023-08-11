<?php

// common functions
///////////////////////////////////////////////////////////////////////////////
function post($destination, $type, $resolution, $fps = null) {
	$post = [
		'id' => trim(`/usr/bin/cat /proc/cpuinfo | /usr/bin/grep Serial | /usr/bin/cut -d ' ' -f 2`),
		'vpn_ip' => trim(`/usr/sbin/ip a show dev tun0 | /usr/bin/grep -oP "inet\s([0-9\.]+)" | /usr/bin/grep -oP "([0-9\.]+)"`),
		'fps' => $fps,
		'resolution' => $resolution
	];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://" . $destination . "/api/post_" . $type . ".php");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_TIMEOUT, 7);
	curl_exec($ch);
	curl_close($ch);
}//function

