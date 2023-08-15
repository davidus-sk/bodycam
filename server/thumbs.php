#!/usr/bin/php
<?php

include 'bootstrap.php';

$db->select('tbl_streams');
$streams = $db->asArray();

if (!empty($streams)) {
	foreach ($streams as $row) {
		$date = date('Y-m-d_H-i-s');

		`/usr/bin/ffmpeg -i tcp://{$row['vpnIp_c']}:12345 -frames:v 1 /data/{$row['id_c']}_{$date}.jpg`;

		if (file_exists("/data/{$row['id_c']}_{$date}.jpg")) {
			$db->insert('tbl_thumbs', [
				'id_c' => $row['id_c'],
				'image_c' => "{$row['id_c']}_{$date}.jpg",
				'date_d' => time(),
			]);
		}//if
	}//foreach
}//if
