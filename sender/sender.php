#!/usr/bin/php
<?php
//apt-get install libgstreamer1.0-dev
//apt-get install gstreamer1.0-tools
//apt-get install gstreamer1.0-plugins-ugly
//apt-get install php-cli php-curl

//HLS: https://forums.raspberrypi.com/viewtopic.php?t=331172
//g-streamer: https://qengineering.eu/install-gstreamer-1.18-on-raspberry-pi-4.html
//https://github.com/bluenviron/mediamtx

// script
///////////////////////////////////////////////////////////////////////////////

set_time_limit(0);

// dont run more than once
///////////////////////////////////////////////////////////////////////////////

// we dont want this to run twice
$lockFile = fopen('/tmp/sender.pid', 'c');
$gotLock = flock($lockFile, LOCK_EX | LOCK_NB, $wouldBlock);
if ($lockFile === false || (!$gotLock && !$wouldBlock)) {
	throw new Exception(date('r') . "> Can't obtain lock.");
} else if (!$gotLock && $wouldBlock) {
	echo date('r') . "> Another instance is already running; terminating.\n";
	exit;
}//if

ftruncate($lockFile, 0);
fwrite($lockFile, getmypid() . "\n");

// get options
///////////////////////////////////////////////////////////////////////////////

$options = getopt("w:h:p:f:s:");

// provide default values
$options['w'] = empty($options['w']) ? 640 : (int)$options['w'];
$options['h'] = empty($options['h']) ? 360 : (int)$options['h'];
$options['p'] = empty($options['p']) ? 12345 : (int)$options['p'];
$options['f'] = empty($options['f']) ? 15 : (int)$options['f'];
$options['s'] = empty($options['s']) ? '10.221.199.1' : $options['s'];

// debug
///////////////////////////////////////////////////////////////////////////////

echo date('r') . "> Starting sender\n";

// common functions
///////////////////////////////////////////////////////////////////////////////
function post($destination, $fps, $resolution) {
	$post = [
		'id' => trim(`/usr/bin/cat /proc/cpuinfo | /usr/bin/grep Serial | /usr/bin/cut -d ' ' -f 2`),
		'vpn_ip' => trim(`ip a show dev tun0 | /usr/bin/grep -oP "inet\s([0-9\.]+)" | /usr/bin/grep -oP "([0-9\.]+)"`),
		'fps' => $fps,
		'resolution' => $resolution
	];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://" . $destination . "/api/post_stream.php");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_TIMEOUT, 7);
	curl_exec($ch);
	curl_close($ch);
}//function

// main loop
///////////////////////////////////////////////////////////////////////////////

// continously check camera updates
while (TRUE) {
	// check if stream is running
	$pid = trim(`/usr/bin/pgrep -f "[l]ibcamera-vid"`);

	if ($pid) {
		// send ping to server
		post($options['s'], $options['f'], $options['w'] . 'x' . $options['h']);

		// stream is good, move on
		echo date('r') . "> Running - $pid\n";
	} else {
		// make sure nothing video related is running
		`/usr/bin/pkill -f "[l]ibcamera-vid"`;
		`/usr/bin/pkill -f "[g]st-launch-1.0"`;

		// launch
		//`libcamera-vid -t 0 --framerate 20 --width 640 --height 360 --inline -o udp://0.0.0.0:12345 > /dev/null 2>&1 &`;
		`/usr/bin/libcamera-vid -t 0 -n --inline --framerate {$options['f']} --width {$options['w']} --height {$options['h']} -o - | /usr/bin/gst-launch-1.0 fdsrc fd=0 ! tcpserversink host=0.0.0.0 port={$options['p']} > /dev/null 2>&1 &`;

		// send ping to server
		post($options['s'], $options['f'], $options['w'] . 'x' . $options['h']);

		echo date('r') . "> Starting\n";
	}//if

	// rest a bit
	sleep(2);
}//while
