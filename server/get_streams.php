<?php

include 'bootstrap.php';

$streamId = !empty($_GET['id']) ? (string)$_GET['id'] : null;

// where conditions
$where = [];
if ($streamId) { $where['id_c'] = $streamId; }

// run query
$db->select('tbl_streams', $where);
$rows = $db->asArray();

$response = [
    'success' => true,
    'count' => count($rows),
    'data' => [],
];

foreach ($rows as $row) {
    $response['data'][] = [
        'id' => $row['id_c'],
        'vpn_ip' => $row['vpnIp_c'],
        'fps' => $row['fps_n'],
        'resolution' => $row['resolution_c'],
        'last_ping' => (int)$row['lastPing_d'],
    ];
}

echo jsonResponse($response);
