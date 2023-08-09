<?php

function jsonResponse($data) {
	header('Content-Type: application/json; charset=utf-8');
	return json_encode($data);
}

function relativeTime($timestamp, $sufix = true) {
	if (!is_numeric($timestamp)) { $timestamp = strtotime($timestamp); }

	$difference = time() - $timestamp;
	$periods = ['sec', 'min', 'hour', 'day', 'week', 'month', 'years', 'decade'];
	$lengths = [60, 60, 24, 7, 4.35, 12, 10, 100];

	if ($difference > 0) { // this was in the past
		$ending = 'ago';
	} else { // this was in the future
		$difference = -$difference;
		$ending = 'to go';
	}

	for ($j = 0; $difference >= $lengths[$j]; $j++) {
		$difference /= $lengths[$j];
	}

	$difference = round($difference);
	if ($difference != 1) $periods[$j] .= 's';

	return $difference . ' ' . $periods[$j] . (($sufix===true) ? ' ' . $ending : '');
}
