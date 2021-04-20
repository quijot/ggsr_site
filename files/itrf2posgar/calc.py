from sys import argv

from geojson import Feature, FeatureCollection, LineString, Point
from geojson import dump as geojson_dump
from gnsstime import gnsstime as gt

from itrf2posgar import (
    calcv10,
    calcv15,
    dd2dms,
    get_best_configuration,
    get_deltas,
    idw_method,
)

try:
    lat = float(argv[1])
    lon = float(argv[2])
    obs_date = argv[3]
except IndexError:
    print('ERROR:\n\t<lat> float, <lon> float and <obs_date> "yyyy-mm-dd".')
    raise
try:
    n = int(argv[4])
    p = int(argv[5])
except IndexError:
    n, p = 3, 1
try:
    lat_comp_dms = argv[6]
    lon_comp_dms = argv[7]
except IndexError:
    lat_comp_dms = lon_comp_dms = ""

obs_date = gt.strptime(obs_date, "%Y-%m-%d")
yy = obs_date.year
doy = obs_date.doy
wk = obs_date.gpsw
lat_dms = dd2dms(lat)
lon_dms = dd2dms(lon)

wk, n, p, delta, dist = get_best_configuration(lat, lon, wk)
lat_idw, lon_idw, nearest, wk = idw_method(lat, lon, wk, n, p)
nearest_report = "Nearest Stations:"
for ep in nearest:
    nearest_report = "{}\n\t{}: {:.2f} km".format(
        nearest_report, ep, nearest[ep].distance / 1000
    )

lat_v10, lon_v10 = calcv10(lat, lon, obs_date)
lat_v15, lon_v15 = calcv15(lat, lon, obs_date)

lat_idw_dms, lon_idw_dms = dd2dms(lat_idw), dd2dms(lon_idw)
lat_v10_dms, lon_v10_dms = dd2dms(lat_v10), dd2dms(lon_v10)
lat_v15_dms, lon_v15_dms = dd2dms(lat_v15), dd2dms(lon_v15)

idw_comp = v10_comp = v15_comp = ""
if lat_comp_dms and lon_comp_dms:
    dd, mm, ss = lat_comp_dms.split()
    lat_comp = int(dd) - int(mm) / 60 - float(ss) / 3600
    dd, mm, ss = lon_comp_dms.split()
    lon_comp = int(dd) - int(mm) / 60 - float(ss) / 3600
    rep = "dist={:.1f}, &Delta;lat={:.1f}, &Delta;lon={:.1f} [cm]"
    idw_comp = rep.format(*get_deltas(lat_idw, lon_idw, lat_comp, lon_comp))
    v10_comp = rep.format(*get_deltas(lat_v10, lon_v10, lat_comp, lon_comp))
    v15_comp = rep.format(*get_deltas(lat_v15, lon_v15, lat_comp, lon_comp))

differences = (
    "\nDiferencia con coordenadas conocidas:\n%s" % idw_comp if idw_comp else ""
)

print(
    """
<h4>Resultado</h4>
<span class='lead'><strong>{lat_idw_dms}, {lon_idw_dms}</strong></span>{differences}""".format(
        **locals()
    )
)

print("<hr><h4>Reporte</strong></h4>")

print(
    "<strong>PPP results</strong> (GPS week {obs_date.gpsw} / year {yy}, day {doy}):\n\
    {lat}, {lon}\t{lat_dms}, {lon_dms}".format(
        **locals()
    )
)

print(
    """
<strong>POSGAR07 coordinates</strong> (IGS05 2006.632):\t\t\
<strong>{lat_comp_dms}  {lon_comp_dms}</strong>\n\
<strong>calc-idw</strong> (weeks_found={wk}, n={n}, p={p})\n\
    {nearest_report}\n\
    {lat_idw:.15f}, {lon_idw:.15f}\t<strong>{lat_idw_dms}, {lon_idw_dms}</strong>\
    {idw_comp}""".format(
        **locals()
    )
)

point_description = "<b>Coordenadas POSGAR07</b><br><b>lat:</b> {}</b><br><b>lon:</b> {}<br><b>alt:</b> {:.3f}"
line_description = "<b>distancia</b>: {:.2f} km"
nearest_geojson = [
    Feature(
        geometry=Point([lon_idw, lat_idw]),
        properties={
            "name": "BASE",
            "description": point_description.format(lat_idw_dms, lon_idw_dms, 0.0),
            "color": "rgba(255, 0, 0, 0.7)",
        },
    )
]
for ep in nearest:
    point = Point([nearest[ep].lon, nearest[ep].lat])
    line = LineString([(lon_idw, lat_idw), (nearest[ep].lon, nearest[ep].lat)])
    point_desc = point_description.format(
        dd2dms(nearest[ep].lat), dd2dms(nearest[ep].lon), nearest[ep].alt
    )
    line_desc = line_description.format(nearest[ep].distance / 1000)
    nearest_geojson.append(
        Feature(
            geometry=point,
            properties={
                "name": ep,
                "description": point_desc,
                "color": "rgba(0, 0, 255, 0.7)",
            },
        )
    )
    nearest_geojson.append(
        Feature(
            geometry=line,
            properties={
                "name": "BASE-" + ep,
                "description": line_desc,
                "color": "green",
            },
        )
    )

fc = FeatureCollection(nearest_geojson)

with open("idw.geojson", "w") as f:
    geojson_dump(fc, f, sort_keys=True)
