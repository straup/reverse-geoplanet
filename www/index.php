<?php

	include("include/init.php");
	loadlib("reverse_geoplanet");
	loadlib("api_output");

	$lat = get_str('lat');
	$lon = get_str('lon');
	$ll = get_str('ll');

	$more = array(
		'inline' => get_str('inline'),
	);

	if ($ll){
		list($lat, $lon) = explode(",", $ll, 2);

		$lat = trim($lat);
		$lon = trim($lon);
	}

	if (($lat == '') || ($lon == '')){
		api_output_error(999, "Missing lat/lon", $more);
	}

	$rsp = reverse_geoplanet($lat, $lon);

	if (! $rsp['ok']){
		api_output_error(999, $rsp['error'], $more);
	}

	api_output_ok($rsp['data'], $more);
	exit();
?>
