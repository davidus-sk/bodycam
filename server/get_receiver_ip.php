<?php

include 'bootstrap.php';

// get receivers
$db->select('tbl_receivers');
$receivers = $db->asArray();

$response = [
    'success' => true,
    'count' => count($receivers),
    'server_time' => time(),
    'data' => [],
];

foreach ($receivers as $row) {
    $response['data'][] = [
        'id' => $row['id_c'],
        'vpn_ip' => $row['vpnIp_c'],
    ];
}//foreach

echo jsonResponse($response);
