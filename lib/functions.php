<?php

// common functions
///////////////////////////////////////////////////////////////////////////////

/**
 * Post status packet to central VPN server
 *
 * @return void
 */
function post($destination, $type, $resolution, $fps = null) {
	$post = [
		'id' => trim(`/usr/bin/cat /proc/cpuinfo | /usr/bin/grep Serial | /usr/bin/cut -d ' ' -f 2`),
		'vpn_ip' => trim(`/usr/sbin/ip a show dev tun0 | /usr/bin/grep -oP "inet\s([0-9\.]+)" | /usr/bin/grep -oP "([0-9\.]+)"`),
		'fps' => $fps,
		'resolution' => $resolution,
		'modem' => get_cellular_data(),
	];

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

	return false;
}//function
