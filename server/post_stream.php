<?php

include 'bootstrap.php';

$response = [
    'success' => true,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		// get request body
		$streamId = !empty($_POST['id']) ? trim($_POST['id']) : null;
		$vpnIp = !empty($_POST['vpn_ip']) ? trim($_POST['vpn_ip']) : null;
		$fps = !empty($_POST['fps']) ? trim($_POST['fps']) : null;
		$resolution = !empty($_POST['resolution']) ? trim($_POST['resolution']) : null;
		$lastPing = !empty($_POST['last_ping']) ? trim($_POST['last_ping']) : null;
		$modem = !empty($_POST['modem']) ? trim($_POST['modem']) : null;
		$uptime = !empty($_POST['uptime']) ? trim($_POST['uptime']) : null;

        $errors = [];
        if (empty($streamId)) {
            $errors[] = sprintf('"%s": must not be null or empty.', 'id');
        }
        if (empty($vpnIp)) {
            $errors[] = sprintf('"%s": must not be null or empty.', 'vpn_ip');
        }
        if (empty($fps)) {
            $errors[] = sprintf('"%s": must not be null or empty.', 'fps');
        }
        if (empty($resolution)) {
            $errors[] = sprintf('"%s": must not be null or empty.', 'resolution');
        }
        if (empty($lastPing)) {
            $lastPing = time();
        }

        // errors
        if ($errors) {
            http_response_code(400);
            $response['errors'] = $errors;
        } else {

            // check if stream exist
            $db->select('tbl_streams', ['id_c' => $streamId]);
            $stream = $db->row_array();

            try {

                // update
                if ($stream) {

                    $db->update('tbl_streams', [
                        'id_c' => $streamId,
                        'vpnIp_c' => $vpnIp,
                        'fps_n' => $fps,
                        'resolution_c' => $resolution,
                        'lastPing_d' => $lastPing,
                        'modem_c' => $modem,
                        'uptime_n' => $uptime,
                    ], ['id_c' => $streamId]);

                    http_response_code(200);

                // create
                } else {
                    $db->insert('tbl_streams', [
                        'id_c' => $streamId,
                        'vpnIp_c' => $vpnIp,
                        'fps_n' => $fps,
                        'resolution_c' => $resolution,
                        'lastPing_d' => $lastPing,
                        'modem_c' => $modem,
                        'uptime_n' => $uptime,
                        'date_d' => time(),
                    ]);

                    http_response_code(201);
                }

                $response['data'] = [
                    'id' => $streamId,
                    'vpn_ip' => $vpnIp,
                    'fps' => $fps,
                    'resolution' => $resolution,
                    'last_ping' => (int)$lastPing,
                ];

            } catch (DatabaseException $e) {

                http_response_code(500);
                $response['success'] = false;
                $response['errors'] = [
                    'Failed to save data. '. $e->getMessage(),
                ];

            }

        }

} else {
    $response['errors'] = ['Request method POST is required.'];
}

// response
echo jsonResponse($response);
