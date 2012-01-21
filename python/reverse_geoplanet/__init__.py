import logging
import json
import urllib2
import sqlite3

class reverse_geoplanet:

    def __init__(self, endpoint):

	# sudo make all this stuff configurable
        
        self.loglevel = logging.WARNING
        self.endpoint = endpoint
	self.timeout = 5
        
        dbconn = sqlite3.connect(':memory:')
        dbcurs = dbconn.cursor()

        dbcurs.execute("""CREATE TABLE reverse_geoplanet (lat DECIMAL, lon DECIMAL, data TEXT)""")
        dbcurs.execute("""CREATE INDEX by_latlon ON reverse_geoplanet (lat, lon)""")
        dbconn.commit()

        handler = logging.StreamHandler()

        log = logging.getLogger('reversegeo')
        log.addHandler(handler)
        log.setLevel(self.loglevel)

        self.dbconn = dbconn
        self.dbcurs = dbcurs
        self.log = log

    def __del__ (self):
    	# sudo figure out a way to write/sync the :memory: db to a local file
    	pass
    
    def reverse_geocode (self, lat, lon):

        short_lat = "%.3f" % lat
        short_lon = "%.3f" % lon

        self.log.debug("reverse geocode for %s, %s" % (short_lat, short_lon))

        self.dbcurs.execute("""SELECT data FROM reverse_geoplanet WHERE lat=? AND lon=?""", (short_lat, short_lon))
        row = self.dbcurs.fetchone()

        if row:
            self.log.debug("return from cache")
            return json.loads(row[0])

        req = "http://%s?ll=%s,%s" % (self.endpoint, lat, lon)

        try:
            rsp = urllib2.urlopen(req, None, self.timeout)
            data = json.loads(rsp.read())
        except Exception, e:
            self.log.error("request for %s failed: %s" % (req, e))
            return None

        if not data:
            self.log.warning("%s returned None" % req)
            return None

        self.dbcurs.execute("""INSERT INTO reverse_geoplanet (lat, lon, data) VALUES(?, ?, ?)""", (short_lat, short_lon, json.dumps(data)))
        self.dbconn.commit()

        self.log.debug("reverse geocoding returns woe id %s" % data['woeid'])
        return data


if __name__ == '__main__':

    import sys
    endpoint = sys.argv[1]

    r = reverse_geoplanet(endpoint)

    print r.reverse_geocode(37.9237,-122.0225)
