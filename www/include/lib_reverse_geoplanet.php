<?php

	loadlib("geohash");
	loadlib("geo_flickr");

	########################################################################

	function reverse_geoplanet($lat, $lon, $remote_endpoint=''){

		# to cache or not to cache?

		if ($remote_endpoint){
			return _reverse_geoplanet_remote($lat, $lon, $remote_endpoint);
		}

		list($short_lat, $short_lon) = _reverse_geoplanet_shorten($lat, $lon);
		$geohash = geohash_encode($short_lat, $short_lon);

		$cache_key = _reverse_geoplanet_cache_key($geohash);

		# try to pull it out of memcache

		$cache = cache_get($cache_key);

		if ($cache['ok']){
			return okay($cache);
		}

		# try to pull it out of the local db

		$enc_hash = AddSlashes($geohash);

		$sql = "SELECT * FROM reverse_geoplanet WHERE geohash='{$enc_hash}'";
		$rsp = db_single(db_fetch($sql));

		if ($rsp){

			cache_set($cache_key, $rsp, "cache locally");

			return okay(array(
				'data' => $rsp
			));
		}

		#

		$loc = geo_flickr_reverse_geocode($lat, $lon);

		if (! $loc){
			return not_okay();
		}

		$woeid = $loc['woeid'];

		$loc = geo_flickr_get_woeid($loc['woeid']);

		if (! $loc){
			return not_okay();
		}

		if (! $loc['woeid']){
			return not_okay();
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

		if (! $rsp['ok']){
			return $rsp;
		}

		return $rsp;
	}

	########################################################################

	function _reverse_geoplanet_remote($lat, $lon, $remote_endpoint){

		$query = http_build_query(array(
			'lat' => $lat,
			'lon' => $lon,
		));

		$url = "{$remote_endpoint}?{$query}";

		$rsp = http_get($url);

		if (! $rsp['ok']){
			return $rsp;
		}

		$data = json_decode($rsp['body'], 'as hash');

		return okay(array(
			'data' => $data,
		));
	}

	########################################################################

	function reverse_geoplanet_add($data, $cache_key=''){

		$insert = array();

		foreach ($data as $key => $value){
			$insert[$key] = AddSlashes($value);
		}

		$rsp = db_insert('reverse_geoplanet', $insert);

		if ($rsp['ok']){

			$cache_key = _reverse_geoplanet_cache_key($data['geohash']);
			cache_set($cache_key, $data, 'cache locally');

			$rsp['data'] = $data;
		}

		return $rsp;
	}

	########################################################################

	function _reverse_geoplanet_shorten($lat, $lon){
		$short_lat = (float)sprintf("%.3f", $lat);
		$short_lon = (float)sprintf("%.3f", $lon);
		return array($short_lat, $short_lon);
	}

	########################################################################

	function _reverse_geoplanet_cache_key($geohash){
		return "reversegeocode_full_{$geohash}";
	}

	########################################################################
?>
