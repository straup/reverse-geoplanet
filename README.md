reverse-geoplanet
--

reverse-geoplanet is a simple web application to reverse geocode a latitude and
longitude using the Flickr reverse geocoder. Results include both the primary
Where On Earth (WOE) ID for the lat,lon and a truncated hierarchy of parent WOE
IDs. Results are cached in both a MySQL instance local to the application as
well as Memcache, if it is installed.

reverse-geoplanet was originally developed for the [building=yes](http://buildingequalsyes.spum.org/) project.

You will need a valid [Flickr API key](http://www.flickr.com/services/apps/create/apply/) in order to use reverse-geoplanet.

How to
--

	http://example.com/reverse-geoplanet/www/?lat=37.765219&lon=-122.419787

	http://example.com/reverse-geoplanet/www/?ll=37.765219,-122.419787

	{
		"latitude":"37.765",
		"longitude":"-122.420",
		"geohash":"9q8yy6bm",
		"woeid":"23512048",
		"locality":"2487956",
		"region":"2347563",
		"country":"23424977",
		"created":"1327172399",
		"name":"Mission Dolores, San Francisco, CA, US, United States",
		"placetype":"22",
		"stat":"ok"
	}

By default reverse-geoplanet outputs JSON and is expected to be run more as a
service for other robots rather than a website for humans. You can force
results to be returned as HTML by specifying the *inline=1* argument. For example:

	http://example.com/reverse-geoplanet/www/?lat=37.765219&lon=-122.419787&inline=1

PHP
--

You can also call the reverse geocoder directly from a Flamwork project like this:

	include("include/init.php");
	loadlib("reverse_geoplanet");

	$lat = 40.721294;
	$lon = -73.983994;
	$remote = 'http://reverse.example.com/';

	dumper(reverse_geoplanet($lat, $lon));
	dumper(reverse_geoplanet($lat, $lon, $remote));

The third argument is optional and tells the code to call another remote
instance of reverse-geoplanet for results.

If you want to bolt the functionality in to another Flamework project itself
you'll need to copy the following files:

* [lib_api_output.php](https://github.com/straup/reverse-geoplanet/blob/master/www/include/lib_api_output.php)
  
* [lib_api_output_json.php](https://github.com/straup/reverse-geoplanet/blob/master/www/include/lib_api_output_json.php)
  
* [lib_flickr_api.php](https://github.com/straup/reverse-geoplanet/blob/master/www/include/lib_flickr_api.php)

* [lib_geo_flickr.php](https://github.com/straup/reverse-geoplanet/blob/master/www/include/lib_geo_flickr.php)
  
* [lib_geohash.php](https://github.com/straup/reverse-geoplanet/blob/master/www/include/lib_geohash.php)
  
* [lib_reverse_geoplanet.php](https://github.com/straup/reverse-geoplanet/blob/master/www/include/lib_reverse_geoplanet.php)
  
(I should probably just write a shell script to automate this...)

Python
--

There is a client-side Python library included that will call your instance of
reverse-geoplanet and also cache the results locally using an in-memory SQLite
database. In case you want to do reverse geocoding from your laptop or
something. Like this:

	$> cd python/reverse_geoplanet

	$> python __init__.py 'example.com/reverse-geoplanet/www'

	{
		u'stat': u'ok',
		u'name': u'Ygnacio Valley, Concord, CA, US, United States',
		u'locality': u'2384020',
		u'woeid': u'55858555',
		u'region': u'2347563',
		u'created': u'1327172577',
		u'longitude': u'-122.022',
		u'placetype': u'22',
		u'geohash': u'9q9pxrf7',
		u'country': u'23424977',
		u'latitude': u'37.924'
	}

Flamework
--

reverse-geoplanet is built on top of [Flamework](https://github.com/straup/flamework) which means if you're poking
around the code you may be wondering what all that other stuff is for. At the
moment: Nothing and it's all disabled. In the future, who knows?

See also
--

* [http://www.flickr.com/services/api/flickr.places.findByLatLon.html](http://www.flickr.com/services/api/flickr.places.findByLatLon.html)
 
* [http://www.flickr.com/services/api/flickr.places.getInfo.html](http://www.flickr.com/services/api/flickr.places.getInfo.html)

* [http://developer.yahoo.com/geo/geoplanet/](http://developer.yahoo.com/geo/geoplanet/)

* [http://buildingequalsyes.spum.org/](http://buildingequalsyes.spum.org/)
  
* [flamework-flickrapp](https://github.com/straup/flamework-flickrapp)

* [flamework-geo](https://github.com/straup/flamework-geo)
