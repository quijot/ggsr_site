from sys import argv

from geojson import Feature, FeatureCollection, LineString, Point
from geojson import dump as geojson_dump
from gnsstime import gnsstime as gt
from itrf2posgar import (
    am1seg,
    ap1seg,
    calcv10,
    calcv15,
    dd2dms,
    get_best_configuration,
    get_deltas,
    idw_method,
)

try:
    ppp_sum_file = argv[1]
except IndexError:
    print("ERROR:\n\tCSRS-PPP summary missing.")
    raise
try:
    n = int(argv[2])
    p = int(argv[3])
except IndexError:
    n, p = 3, 1
try:
    lat_comp_dms = argv[4]
    lon_comp_dms = argv[5]
except IndexError:
    lat_comp_dms = lon_comp_dms = ""


with open(ppp_sum_file, "r") as fd:
    summary = fd.readlines()

for l in summary:
    if l.startswith("POS LAT"):
        syst = l.split()[2]
        yy, doy, etc = l.split()[3].split(":")
        d = gt.from_doy(int(yy), int(doy))
        wk = d.gpsw
        latd, latm, lats, lat_diff, lat_sigma = l.split()[7:12]
        lat_dms = "{} {} {}".format(latd, latm, lats)
        lat = int(latd) - int(latm) / 60 - float(lats) / 3600
        lat_prec = float(lat_sigma)
    elif l.startswith("POS LON"):
        lond, lonm, lons, lon_diff, lon_sigma = l.split()[7:12]
        lon_dms = "{} {} {}".format(lond, lonm, lons)
        lon = int(lond) - int(lonm) / 60 - float(lons) / 3600
        lon_prec = float(lon_sigma)

# PPP results
lat_prec_s = round(lat_prec / am1seg(lat, lon), 6)
lon_prec_s = round(lon_prec / ap1seg(lat, lon), 6)
url = '<a href="{}">{}</a>'
url_sum = url.format(ppp_sum_file, "summary")
ppp_pdf_file = ppp_sum_file.replace(".sum", ".pdf")
url_pdf = url.format(ppp_pdf_file, "report")
ppp_full_output = ppp_sum_file.replace(".sum", ".{}d_full_output.zip".format(yy))
url_full = url.format(ppp_full_output, "full output")

wk, n, p, delta, dist = get_best_configuration(lat, lon, wk)
lat_idw, lon_idw, nearest, wk = idw_method(lat, lon, wk, n, p)
nearest = {
    ep: nearest[ep] for ep, data in sorted(nearest.items(), key=lambda x: x[1].distance)
}
nearest_report = "Nearest Stations:"
for ep in nearest:
    nearest_report = "{}\n\t{}: {:.2f} km".format(
        nearest_report, ep, nearest[ep].distance / 1000
    )

lat_v10, lon_v10 = calcv10(lat, lon, d)
lat_v15, lon_v15 = calcv15(lat, lon, d)

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
    '<strong>CSRS-PPP results</strong> ({syst} - GPS week {wk} / year 20{yy}, day {doy}):\n\
    {lat} &plusmn; {lat_prec:.4f}m, {lon} &plusmn; {lon_prec:.4f}m\n\
    {lat_dms} &plusmn; {lat_prec_s:.6f}", {lon_dms} &plusmn; {lon_prec_s:.6f}"\n\
    {url_sum}, {url_pdf}, {url_full} (available for the next 12hs)\
    '.format(
        **locals()
    )
)

print(
    """
<strong>POSGAR07 coordinates</strong> (IGS05 2006.632):\t\t\
<strong>{lat_comp_dms}  {lon_comp_dms}</strong>\n\
<strong>calc-idw</strong> (weeks_found={wk}, n={n}, p={p})\n\
    {nearest_report}\n\
    {lat_idw:.15f}, {lat_idw:.15f}\t<strong>{lat_idw_dms}, {lon_idw_dms}</strong>\
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
