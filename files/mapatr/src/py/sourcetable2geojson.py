#!/usr/bin/env python
# -*- coding: UTF-8 -*-

from sys import argv
from os import system, SEEK_END


# Usage:
# $ python sourcetable2geojson.py <caster-name> <url-sourcetable> <layer-color>
caster_name = argv[1]
url = argv[2]
color = argv[3] # layer color
align = argv[4] # text align
bline = argv[5] # text baseline
geojson = '%s.geojson' % caster_name
sourcetable = '%s.sourcetable' % caster_name
system("wget -O %s %s" % (sourcetable, url))

f = open(geojson, 'w')
f.write("""{
  "type": "FeatureCollection",
  "crs": {
    "type": "name",
    "properties": {
      "name": "urn:ogc:def:crs:OGC:1.3:CRS84"
    }
  },
  "features": [
""")
  
st = open(sourcetable, 'r')
for line in st:
  if line.startswith('STR'):
    array = line.split(';')
    mountpoint  = array[1]
    identifier  = array[2]
    data_format = array[3]
    format_det  = array[4]
    carrier     = 'No info' if(array[5] != '0') else 'L%s' % array[5]
    nav_system  = array[6]
    network     = array[7]
    country     = array[8]
    latitude    = float(array[9])
    longitude   = float(array[10])
    nmea        = array[11]
    solution    = 'network' if(array[12] != '0') else 'single base'
    generator   = array[13]
    compression = array[14]
    authenticat = array[15]
    fee         = array[16]
    bitrate     = array[17]
    misc        = '' if(len(array)<=18) else array[18].strip()

    f.write("""    {
      "type": "Feature",
      "properties": {
        "name": "%s",
        "identifier": "%s",
        "data_format": "%s",
        "format_details": "%s",
        "carrier": "%s",
        "nav_system": "%s",
        "network": "%s",
        "country": "%s",
        "coordinates": "%s, %s",
        "solution": "%s",
        "misc": "%s",
        "color": "%s",
        "align": "%s",
        "bline": "%s"
      },
      "geometry": {
        "type": "Point",
        "coordinates": [%s, %s]
      }\n    },\n""" % (mountpoint, 
      identifier, 
      data_format, 
      format_det, 
      carrier, 
      nav_system, 
      network, 
      country, 
      latitude, longitude,
      solution, 
      misc, 
      color, align, bline,
      longitude, latitude))
st.close()

f.close()
f = open(geojson,'rb+')
f.seek(-2, SEEK_END)
f.truncate()
f.close()
f = open(geojson,'a')
f.write("\n  ]\n}\n")
f.close()
