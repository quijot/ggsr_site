from collections import namedtuple
from datetime import date
from math import copysign, trunc

from geodata import iws, ramsac
from geographiclib.geodesic import Geodesic
from gnsstime import gnsstime as gt


def dd2dms(coord):
    """
    Convert coordinates from Geocentric Cartesians to Geodetic:
    input:
        x, y, z (meters)
    output:
        lat, lon (deegres), alt (meters)
    """
    dd = trunc(coord)
    mm_ = (coord - dd) * 60
    mm = trunc(mm_)
    ss = (mm_ - mm) * 60
    return "{} {:02d} {:.5f}".format(dd, abs(mm), round(abs(ss), 5))


def geodetic_distance(lat1, lon1, lat2, lon2):
    """Distancia geodésica en metros entre (lat1,lon1) y (lat2,lon2)"""
    return Geodesic.WGS84.Inverse(lat1, lon1, lat2, lon2)["s12"]


def get_deltas(lat, lon, lat_comp, lon_comp):
    """Obtener diferencias/deltas entre dos puntos (en cm)"""
    vector = geodetic_distance(lat, lon, lat_comp, lon_comp)
    lat_diff = geodetic_distance(lat, lon, lat_comp, lon)
    lon_diff = geodetic_distance(lat, lon, lat, lon_comp)
    return vector * 100, lat_diff * 100, lon_diff * 100


def am1seg(lat, lon):
    """Arco de Meridiano de 1 segundo para Latitud=lat"""
    return geodetic_distance(lat, lon, lat + 1 / 3600, lon)


def ap1seg(lat, lon):
    """Arco de Paralelo de 1 segundo para Latitud=lat"""
    return geodetic_distance(lat, lon, lat, lon + 1 / 3600)


def idw(values, distances, p=1):
    """
    Calculate Inverse Distance Weighted interpolation of
    <values> based on <distances> weighting it with <p>.
        <values> iterable of floats
        <distances> iterable of floats
        <p> integer
    """
    distances = [1e-100 if d == 0 else d for d in distances]
    sum_vd = sum([v / d ** p for v, d in zip(values, distances)])
    sum_1d = sum([1 / d ** p for d in distances])
    if sum_1d == 0:
        return 0
    else:
        return sum_vd / sum_1d


def difference_in_years(date_from, date_to):
    """
    Returns difference between dates expressed in years and fraction.
        <date_from> and <date_to> can be date/datetime/gnsstime type but
        also can be int or float already converted to year and fraction.
    """
    df = (
        year_fraction(date_from)
        if not isinstance(date_from, (int, float))
        else date_from
    )
    dt = year_fraction(date_to) if not isinstance(date_to, (int, float)) else date_to
    return df - dt


def year_fraction(date):
    """
    Returns year and fraction expression of the date, ie. 2006.632.
        <date> must be date/datetime/gnsstime type
    """
    doy = gt(date.year, date.month, date.day).doy
    total_days_in_year = gt(date.year, 12, 31).doy
    years = date.year + doy / total_days_in_year
    return years


def get_around_values(grid, lat, lon, alt=None):
    """
    Get up to 4 points around (<lat>,<lon>) from <grid> and return
    corresponding values and distances from (<lat>,<lon>) to each point.
    For example:
        if (-31.3, -61.7)
        it will return values corresponding to
        (-31,-62)   (-31,-61)
        (-32,-62)   (-32,-61)
        and distances from (-31.3, -61.7) to each.
    """
    # surroundings
    lat0, lon0 = trunc(lat), trunc(lon)
    lat1, lon1 = lat0 + copysign(1, lat), lon0 + copysign(1, lon)
    points = [(lat0, lon0), (lat0, lon1), (lat1, lon0), (lat1, lon1)]

    Around = namedtuple("Around", "points values_n values_e values_h distances")
    # only if each point (b, l) exists in grid
    Around.points = [(b, l) for (b, l) in points if l in grid and b in grid[l]]
    Around.values_n = [grid[l][b]["n"] for (b, l) in Around.points]
    Around.values_e = [grid[l][b]["e"] for (b, l) in Around.points]
    Around.values_h = [grid[l][b]["h"] for (b, l) in Around.points] if alt else None
    Around.distances = [
        geodetic_distance(lat, lon, lat0, lon0) for (b, l) in Around.points
    ]
    return Around


def apply_grid(
    grid, lat, lon, alt=None, date_from=None, date_to=None, interpolate=True
):
    """
    Apply <grid> values to (<lat>,<lon>,<alt>) for the timerange
    <date_from>-<date_to>, interpolating with <interpolation_method>.
        <grid>                  dict
        <lat>,<lon>,<alt>       floats
        <date_from>,<date_to>   date/datetime/gnsstime or int or float
    """
    years = difference_in_years(date_from, date_to) if date_from and date_to else 1
    am = am1seg(lat, lon)
    ap = ap1seg(lat, lon)

    # calcular correciones
    if not interpolate:
        latd = round(lat)
        lond = round(lon)
        lat_c = grid[lond][latd]["n"]
        lon_c = grid[lond][latd]["e"]
        if alt:
            alt_c = grid[lond][latd]["h"]
    else:
        around = get_around_values(grid, lat, lon)
        lat_c = idw(around.values_n, around.distances)
        lon_c = idw(around.values_e, around.distances)
        if alt:
            alt_c = idw(around.values_h, around.distances)

    # aplicar correcciones
    lat -= lat_c / am * years / 3600
    lon -= lon_c / ap * years / 3600
    if alt:
        alt -= alt_c * years
    else:
        alt = None

    return lat, lon, alt


def apply_vms2017(
    lat, lon, epoch_from=date(2017, 1, 28), epoch_to=date(2014, 1, 1), interpolate=True
):
    from vms2017 import vel as grid

    lat, lon, alt = apply_grid(grid, lat, lon, None, epoch_from, epoch_to, interpolate)
    return lat, lon


def apply_vms2015(
    lat, lon, epoch_from=date(2015, 4, 11), epoch_to=date(2010, 3, 14), interpolate=True
):
    from vms2015 import vel as grid

    lat, lon, alt = apply_grid(grid, lat, lon, None, epoch_from, epoch_to, interpolate,)
    return lat, lon


def apply_vms2009(
    lat, lon, epoch_from=date(2009, 6, 30), epoch_to=date(2000, 1, 2), interpolate=True
):
    from vms2009 import vel as grid

    lat, lon, alt = apply_grid(grid, lat, lon, None, epoch_from, epoch_to, interpolate,)
    return lat, lon


def apply_sumBL(lat, lon, interpolate=True):
    from sumBL import disp as grid

    lat, lon, alt = apply_grid(grid, lat, lon, None, interpolate)
    return lat, lon


def apply_velar2015_2007(lat, lon, interpolate=True):
    from velar2015a2007 import disp as grid

    lat, lon, alt = apply_grid(grid, lat, lon, None, interpolate)
    return lat, lon


def idw_method(lat, lon, wk, n=3, p=1, weeks_around=3, ws=None, ref_stations=None):
    if ref_stations is None:
        ref_stations = ramsac

    # if ws is set, se supone que me pasaron las soluciones adecuadas para la semana wk
    if ws is None:
        ws = iws

        # find nearest week in ws
        while wk not in ws:
            wk -= 1
        # add some more weeks to the solution set
        # if weeks near wk are in ws add it to ws[wk], preferring ws[wk]
        # nearest weeks have preference wk+-1, wk+-2, etc, in that order
        weeks = []
        for i in range(1, weeks_around + 1):
            weeks.append(wk + i)
            weeks.append(wk - i)
        for w in weeks:
            if w in ws:
                # ws[wk] = {**ws[w], **ws[wk]}  # <-- Python >=3.6
                ws[wk] = dict(ws[w], **ws[wk])
        ws = ws[wk]

    # find N nearest EP in ref_stations
    distance_to = {
        ep: geodetic_distance(lat, lon, coord["lat"], coord["lon"])
        for ep, coord in ref_stations.items()
        if ep in ws
    }
    Nearest = namedtuple("Nearest", "distance delta_lat delta_lon name lat lon alt")
    nearest = {
        ep: Nearest(
            d,
            ws[ep]["lat"] - ref_stations[ep]["lat"],
            ws[ep]["lon"] - ref_stations[ep]["lon"],
            ep,
            ref_stations[ep]["lat"],
            ref_stations[ep]["lon"],
            0.0 if "alt" not in ref_stations[ep] else ref_stations[ep]["alt"],
        )
        for ep, d in sorted(distance_to.items(), key=lambda i: i[1])[:n]
    }

    distances, delta_lat, delta_lon, *rest = zip(*nearest.values())

    lat_interpolated = lat - idw(delta_lat, distances, p)
    lon_interpolated = lon - idw(delta_lon, distances, p)

    return lat_interpolated, lon_interpolated, nearest, wk


def calcv10(lat, lon, epoch, interpolate=True):
    lat, lon = apply_vms2015(lat, lon, epoch, 2011.322, interpolate)
    return apply_sumBL(lat, lon, interpolate)


def calcv15(lat, lon, epoch, interpolate=True):
    lat, lon = apply_vms2017(lat, lon, epoch, 2015, interpolate)
    return apply_velar2015_2007(lat, lon, interpolate)


# -------------------------------
# IDW helpers
# -------------------------------
def configurations():
    return [
        (1, 1),
        (2, 1),
        (2, 2),
        (3, 1),
        (3, 2),
        (3, 3),
        (5, 1),
        (5, 2),
        (5, 3),
        (7, 1),
        (7, 2),
        (7, 3),
        (10, 1),
        (10, 2),
        (10, 3),
        (20, 1),
        (20, 2),
        (20, 3),
        (30, 1),
        (30, 2),
        (30, 3),
        (50, 1),
        (50, 2),
        (50, 3),
        (50, 5),
    ]


def test(ep, wk, n=3, p=1):
    if ep in iws[wk]:
        temp = iws[wk].copy()
        st = temp.pop(ep)
        lat, lon, n, wk = idw_method(st["lat"], st["lon"], wk=wk, n=n, p=p, ws=temp)
        d, dlat, dlon = get_deltas(ramsac[ep]["lat"], ramsac[ep]["lon"], lat, lon)
        return d
    return None


def best_configuration(ep, wk):
    # find nearest week in ws
    while wk not in iws:
        wk -= 1
    best_result = 999
    best_conf = (3, 1)
    if ep in iws[wk]:
        for n, p in configurations():
            d = test(ep, wk, n, p)
            if d is not None and d < best_result:
                best_result = d
                best_conf = n, p
    if best_result == 999:
        best_result = None
    n, p = best_conf
    return wk, n, p, best_result


def get_best_configuration(lat, lon, wk):
    # find nearest week in ws
    while wk not in iws:
        wk -= 1
    # find nearest EP in ramsac and sirgas
    distance_to = [
        (geodetic_distance(lat, lon, coord["lat"], coord["lon"]), ep)
        for ep, coord in ramsac.items()
        if ep in iws[wk]
    ]
    dist, ep = min(distance_to)
    wk, n, p, best_result = best_configuration(ep, wk)
    return wk, n, p, best_result, dist


def test_ep(ep):
    results = {"modules": 0, "weeks": 0}
    for wk in iws:
        wk, n, p, dist = best_configuration(ep, wk)
        if dist is not None:
            print("wk={wk} n={n} p={p} {dist:.1f}".format(**locals()))
            results["modules"] += dist
            results["weeks"] += 1
    media = results["modules"] / results["weeks"]
    print("Media={:.1f}".format(media))
    print("Weeks={weeks}".format(**results))
    return media
