#!/usr/bin/php
<?php

// RECEIVER script
///////////////////////////////////////////////////////////////////////////////

set_time_limit(0);

// shared functions
include __DIR__ . '/../lib/functions.php';

// we dont want this to run twice
$lockFile = fopen('/tmp/receiver.pid', 'c');
$gotLock = flock($lockFile, LOCK_EX | LOCK_NB, $wouldBlock);
if ($lockFile === false || (!$gotLock && !$wouldBlock)) {
	throw new Exception(date('r') . "> Can't obtain lock.");
} else if (!$gotLock && $wouldBlock) {
	echo date('r') . "> Another instance is already running; terminating.\n";
	exit;
}//if

ftruncate($lockFile, 0);
fwrite($lockFile, getmypid() . "\n");

// get settings
$hdmi = hdmi_display_status();

list($w, $h) = hdmi_screen_size();

// debug
echo date('r') . "> Starting receiver\n";
echo date('r') . "> Screen size: {$w}x{$h}\n";

$quad = screen_quadrants($w, $h);

echo date('r') . "> Quadrants at: " . json_encode($quad) . "\n";

$streams = [];

// continously check camera updates
while (TRUE) {
	$running = trim(`/usr/bin/pgrep -f "Stream: [0-9a-z]+"`);
	$pids = [];

	if (!empty($running)) {
		$pids = explode("\n", $running);

		echo date('r') . "> Running streams: " . join(',', $pids) . "\n";
	}//if

	// get streaming data from the relay server
	$json = file_get_contents('http://10.220.0.1/api/get_streams.php');

	if ($data = json_decode($json, TRUE)) {
		foreach ($data['data'] as $s) {
			$id = $s['id'];
			$ts = $s['last_ping'];
			$ip = $s['vpn_ip'];
			$fps = $s['fps'];
			$diff = time() - $ts;

			echo date('r') . "> Camera {$id} at {$ip} last seen $ts ({$diff})\n";

			// check if stream is running
			$pid = trim(`/usr/bin/pgrep -f "[S]tream: $id"`);

			if ($pid) {
				// is the stream stale?
				if ($diff > 5) {
					`/usr/bin/pkill -9 -f "[S]tream: $id"`;
					echo date('r') . "> Killing stale stream - $id\n";

					unset($pids[array_search($pid, $pids)]);
					unset($streams[$id]);

					continue;
				}//if

				// stream is good, move on
				echo date('r') . "> Running - $pid\n";

				unset($pids[array_search($pid, $pids)]);
			} else {
				// not running, determine free locations
				foreach ($quad as $q) {
					$l = trim(`/usr/bin/pgrep -f "[-]left $q[0] -top $q[1]"`);
					if (empty($l)) {
						$bw = $w / 2;
						$bh = $h / 2;

						`DISPLAY=:0 /usr/bin/ffplay tcp://{$ip}:12345 -vf "setpts=N/{$fps}" -loglevel quiet -stats -hide_banner -fflags nobuffer -flags low_delay -framedrop -left {$q[0]} -top {$q[1]} -window_title "Stream: $id" -x {$bw} -y {$bh} -noborder > /dev/null 2>&1 &`;
						echo date('r') . "> Launching $id on [{$q[0]},{$q[1]}]\n";

						$pid = trim(`/usr/bin/pgrep -f "[S]tream: $id"`);
						unset($pids[array_search($pid, $pids)]);


						$streams[$id] = $pid;

						sleep(2);
						break;
					}//if
				}//foreach
			}//if
		}//foreach

		if (!empty($pids)) {
			foreach ($pids as $p) {
				`/usr/bin/kill -9 $p`;

				echo date('r') . "> Killing orphaned stream - $p\n";

				unset($pids[array_search($p, $pids)]);
				unset($streams[$id]);
			}//if

			unset($pids);
		}//if
	}//if

	// notify
	if ((time() % 5) == 0) {
		if (($hdmi == false) && hdmi_display_status()) {
			list($w, $h) = hdmi_screen_size();
			$quad = screen_quadrants($w, $h);
		}//if

		$hdmi = hdmi_display_status();

		post('10.220.0.1', 'receiver', ['resolution' => $w . 'x' . $h, 'streams' => json_encode($streams), 'monitor' => $hdmi]);
	}//if

	sleep(2);
}//while
