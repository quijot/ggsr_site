from geojson import Feature, FeatureCollection, Point, dump

from geodata import iws, ramsac


def get_geojson(eps, color="rgba(255, 0, 0, 0.7)"):
    point_description = "<b>Coordenadas POSGAR07</b><br>\
    <b>lat:</b> {lat}</b><br>\
    <b>lon:</b> {lon}<br>"
    ep_geojson = []
    for ep, coord in eps.items():
        ep_geojson.append(
            Feature(
                geometry=Point([coord["lon"], coord["lat"]]),
                properties={
                    "name": ep,
                    "description": point_description.format(**coord),
                    "color": color,
                },
            )
        )
    return FeatureCollection(ep_geojson)


def get_geojson_ramsac():
    fc = get_geojson(ramsac, color="rgba(0, 0, 255, 0.7)")
    with open("ep.geojson", "w") as f:
        dump(fc, f, sort_keys=True)


def get_geojson_common(wk=None):
    if wk is None:
        common = set()
        for wk, sol in iws.items():
            common.update(sol.keys())
    else:
        common = iws[wk].keys()

    common = {ep: coord for ep, coord in ramsac.items() if ep in common}

    fc = get_geojson(common, color="rgba(0, 255, 0, 0.7)")

    with open("ep.geojson", "w") as f:
        dump(fc, f, sort_keys=True)
