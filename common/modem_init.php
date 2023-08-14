#!/usr/bin/php
<?php

include __DIR__ . '/../lib/functions.php';

for($i = 0; $i < 20; $i++) {
	$code = set_network_type();

	echo date('r') . "> Return code: {$code}\n";

	if ($code == 200) {
		exit;
	}//if

	sleep(5);
}//for
