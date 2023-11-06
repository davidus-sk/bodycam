<?php
// common files
///////////////////////////////////////////////////////////////////////////////
$config_file = '/app/bodycam/config/settings.json';

// common functions
///////////////////////////////////////////////////////////////////////////////

function get_default_interface() {
	if (file_exists($config_file)) {
		if ($data = json_decode(file_get_contents($config_file), TRUE)) {
			if (!empty($data['interface'])) {
				return trim($data['interface']);
			}//if
		}//if
	}//if

	return 'tun0';
}//function

/**
 * Post status packet to central VPN server
 *
 * @return void
 */
function post($destination, $type, $data = null) {
	$interface = get_default_interface();

	$post = [
		'id' => trim(`/usr/bin/cat /proc/cpuinfo | /usr/bin/grep Serial | /usr/bin/cut -d ' ' -f 2`),
		'vpn_ip' => trim(`/usr/sbin/ip a show dev $interface | /usr/bin/grep -oP "inet\s([0-9\.]+)" | /usr/bin/grep -oP "([0-9\.]+)"`),
		'uptime' => trim(`/usr/bin/cat /proc/uptime | /usr/bin/cut -d ' ' -f 1`),
		'modem' => json_encode(get_cellular_data()),
	];

	$post = array_merge($post, $data);

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

/**
 * Create SDP streaming file
 *
 * @return void
 */
function create_sdp_file($id, $port) {
	$interface = get_default_interface();

	$receiver_ip = trim(`/usr/sbin/ip a show dev $interface | /usr/bin/grep -oP "inet\s([0-9\.]+)" | /usr/bin/grep -oP "([0-9\.]+)"`);

	$contents = "v=0\no=- {$id} 0 IN IP4 {$receiver_ip}\ns=Stream: {$id}\nc=IN IP4 {$receiver_ip}\nt=0 0\nm=video {$port} RTP/AVP 96\na=rtpmap:96 H264/90000\n";

	file_put_contents("/tmp/{$id}.sdp", $contents);
}//function

/**
 * Get receiver IP address
 *
 * @return string
 */
function get_receiver_ip($destination) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://" . $destination . "/api/get_receiver_ip.php");
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_TIMEOUT, 7);
	$json = curl_exec($ch);
	curl_close($ch);

	if ($data = json_decode($json, TRUE)) {
		// for not return first one
		if ($data['count'] > 0) {
			return $data['data'][0]['vpn_ip'];
		}//if
	}//if

	return NULL;
}//function

/**
 * Get info from the cellular modem
 *
 * @return bool|array
 */
function get_cellular_data() {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://192.168.0.1/goform/goform_get_cmd_process?multi_data=1&isTest=false&sms_received_flag_flag=0&sts_received_flag_flag=0&cmd=modem_main_state%2Cpin_status%2Copms_wan_mode%2Cloginfo%2Cnew_version_state%2Ccurrent_upgrade_state%2Cis_mandatory%2Cwifi_dfs_status%2Cbattery_value%2Cppp_dial_conn_fail_counter%2Csignalbar%2Cnetwork_type%2Cnetwork_provider%2Cppp_status%2CEX_SSID1%2Csta_ip_status%2CEX_wifi_profile%2Cm_ssid_enable%2CRadioOff%2CSSID1%2Csimcard_roam%2Clan_ipaddr%2Cstation_mac%2Cbattery_charging%2Cbattery_vol_percent%2Cbattery_pers%2Cspn_name_data%2Cspn_b1_flag%2Cspn_b2_flag%2Crealtime_tx_bytes%2Crealtime_rx_bytes%2Crealtime_time%2Crealtime_tx_thrpt%2Crealtime_rx_thrpt%2Cmonthly_rx_bytes%2Cmonthly_tx_bytes%2Cmonthly_time%2Cdate_month%2Cdata_volume_limit_switch%2Cdata_volume_limit_size%2Cdata_volume_alert_percent%2Cdata_volume_limit_unit%2Croam_setting_option%2Cupg_roam_switch%2Cssid%2Cwifi_enable%2Cwifi_5g_enable%2Ccheck_web_conflict%2Cdial_mode%2Cwifi_onoff_func_control%2Cppp_dial_conn_fail_counter%2Cwan_connect_status%2Cwan_lte_ca%2Cprivacy_read_flag%2Csms_received_flag%2Csts_received_flag%2Csms_unread_num&_=1691879410233");
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_TIMEOUT, 4);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		"Referer: http://192.168.0.1/index.html",
		"X-Requested-With: XMLHttpRequest",
		"Accept: application/json, text/javascript, */*; q=0.01"
	]);
	$json = curl_exec($ch);
	curl_close($ch);

	if ($data = json_decode($json, true)) {
		return [
			"signalbar" => $data['signalbar'],
			"network_type" => $data['network_type'],
			"network_provider" => $data['network_provider'],
			"ppp_status" => $data['ppp_status'],
			"realtime_tx_thrpt" => $data['realtime_tx_thrpt'],
			"realtime_rx_thrpt" => $data['realtime_rx_thrpt']
		];
	}//if

	return [];
}//function

/**
 * Set network type mode to automatic
 *
 * @return int
 */
function set_network_type()
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://192.168.0.1/goform/goform_set_cmd_process");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "isTest=false&goformId=SET_BEARER_PREFERENCE&BearerPreference=NETWORK_auto");
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
	curl_setopt($ch, CURLOPT_TIMEOUT, 4);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		"Referer: http://192.168.0.1/index.html",
		"X-Requested-With: XMLHttpRequest",
		"Accept: application/json, text/javascript, */*; q=0.01"
	]);
	curl_exec($ch);

	if (!curl_errno($ch)) {
		return (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
	}//if

	curl_close($ch);

	return -1;
}//function

/**
 * Check if monitor is connected
 *
 * @return bool
 */
function hdmi_display_status()
{
	// Connector 0 (32) HDMI-A-1 (connected)
	$status = `/usr/bin/kmsprint`;

	if (preg_match('/Connector 0 \([0-9]+\) HDMI-A-1 \(([a-z]+)\)/', $status, $m)) {
		if ($m[1] == 'connected') {
			return true;
		}//if
	}//if

	return false;
}//function

/**
 * Get display resolution
 *
 * @return array
 */
function hdmi_screen_size()
{
	// get settings
	$x = trim(`DISPLAY=:0 /usr/bin/xrandr | grep '*'`);
	$w = 0;
	$h = 0;

	//    1920x1080     60.00*+  50.00    59.94
	if (preg_match('/([0-9]+)x([0-9]+)/', $x, $m)) {
		$w = (int)$m[1];
		$h = (int)$m[2];
	}//if

	return [$w, $h];
}//func

function screen_quadrants($w, $h)
{
	$quad = [];

	for ($i = 0; $i <= 1; $i++) {
		for ($j = 0; $j <= 1; $j++) {
			$quad[] = [$i*$w/2, $j*$h/2];
		}//for
	}//for

	return $quad;
}//function
