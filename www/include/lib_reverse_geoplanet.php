<?php

	loadlib("geohash");
	loadlib("geo_flickr");

	########################################################################

	function reverse_geoplanet($lat, $lon){

		# TODO: check for a config flag that indicates we want to call a
		# remote instance of reverse-geoplanet and then do that.

		$short_lat = (float)sprintf("%.3f", $lat);
		$short_lon = (float)sprintf("%.3f", $lon);

		$geohash = geohash_encode($short_lat, $short_lon);

		$cache_key = "reversegeocode_full_{$geohash}";

		# try to pull it out of memcache

		$cache = cache_get($cache_key);

		if ($cache['ok']){
			return $cache['data'];
		}

		# try to pull it out of the local db

		$enc_hash = AddSlashes($geohash);

		$sql = "SELECT * FROM reverse_geoplanet WHERE geohash='{$enc_hash}'";
		$rsp = db_single(db_fetch($sql));

		if ($rsp){
			cache_set($cache_key, $rsp, "cache locally");
			return $rsp;
		}

		#

		$loc = geo_flickr_reverse_geocode($lat, $lon);

		if (! $loc){
			return null;
		}

		$woeid = $loc['woeid'];

		$loc = geo_flickr_get_woeid($loc['woeid']);

		if (! $loc){
			return null;
		}

		if (! $loc['woeid']){
			return null;
		}

		#

		$hierarchy = array();

		foreach (array('locality', 'region', 'country') as $placetype){
			$hierarchy[$placetype] = $loc[$placetype]['woeid'];
		}

		$now = time();

		$data = array(
			'latitude' => $short_lat,
			'longitude' => $short_lon,
			'geohash' => $geohash,
			'woeid' => $loc['woeid'],
			'locality' => $hierarchy['locality'],
			'region' => $hierarchy['region'],
			'country' => $hierarchy['country'],
			'name' => $loc['name'],
			'placetype' => $loc['place_type_id'],
			'created' => $now,
		);

		$rsp = reverse_geoplanet_add($data);

		if ($rsp['ok']){
			return $rsp['data'];
		}
	}

	########################################################################

	function reverse_geoplanet_add($data){

		$insert = array();

		foreach ($data as $key => $value){
			$insert[$key] = AddSlashes($value);
		}

		$rsp = db_insert('reverse_geoplanet', $insert);

		if ($rsp['ok']){
			cache_set($cache_key, $insert, 'cache locally');
			$rsp['data'] = $data;
		}

		return $rsp;
	}

	########################################################################
?>
