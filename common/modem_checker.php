#!/usr/bin/php
<?php

include __DIR__ . '/../lib/functions.php';

while(true) {
	$data = get_cellular_data();

	if (!empty($data)) {
		if ($data['ppp_status'] != "ipv4_ipv6_connected") {
			echo date('r') . '> Trying to reconnect. Current status: ' . $data['ppp_status'] . "\n";
			set_network_type();

			sleep(15);
		}//if
	}//if

	sleep(2);
}//while
