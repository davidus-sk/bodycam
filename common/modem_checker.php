#!/usr/bin/php
<?php

include __DIR__ . '/../lib/functions.php';

while(true) {
	$data = get_cellular_data();

	if (!empty($data)) {
		if ($data['ppp_status'] != "ipv4_ipv6_connected") {
			set_network_type();
		}//if
	}//if

	sleep(2);
}//while
