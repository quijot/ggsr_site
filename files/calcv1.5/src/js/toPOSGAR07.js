/******************************************************************************
 * toPOSGAR07
 * Corregir coordenadas geodésicas ITRF y expresarlas en POSGAR07.
 * Aplica correcciones según modelos de velocidades VMS2017 y VelAR.
 * Autor:    Santiago Pestarini <santiagonob@gmail.com>
 * Licencia: MIT
 * Utiliza:  GeographicLib
 *****************************************************************************/
/*
 Usage:

 Parameters
  lat/lon:         -dd.ddd | -dd mm ss.sss
  obsDate:         'yyyy-mm-dd'
  repCont: html element for logging output (optional)

 For example
  // set report container (optional)
  repCont = document.getElementById('report');
  // call toPOSGAR07 function
  [latC, lonC] = toPOSGAR07(lat, lon, obsDate, repCont);
  
  IMPORTANT, must include this scripts as follows:
  <script type="text/javascript" src="http://geographiclib.sourceforge.net/scripts/geographiclib.js"></script>
  <script type="text/javascript" src="/assets/js/vms2015.js"></script>
  <script type="text/javascript" src="/assets/js/sumBL.js"></script>
 */
function toPOSGAR07(lat, lon, obsDate, repCont) {
  var r, geod = GeographicLib.Geodesic.WGS84;
  var DMS = GeographicLib.DMS;
  report(null, repCont);
  // coord -----------------------------------------------------------------
  var latDMS = DMS.Encode(lat, DMS.SECOND, 5, DMS.LATITUDE);
  var lonDMS = DMS.Encode(lon, DMS.SECOND, 5, DMS.LONGITUDE);
  report("Latitud y Longitud ITRF:", repCont);
  report("\t".concat(lat).concat("\t").concat(latDMS), repCont);
  report("\t".concat(lon).concat("\t").concat(lonDMS), repCont);
  // years -----------------------------------------------------------------
  var startEpoch = 2015.0; // reference date
  var epoch = getYearFraction(obsDate);
  var years = epoch - startEpoch;
  report("Fecha de medición:", repCont);
  report("\t".concat(obsDate).concat("\tépoca ").concat(epoch.toFixed(3)), repCont);
  report("Tiempo desde época ".concat(startEpoch).concat(":"), repCont);
  report("\t".concat(years.toFixed(3)).concat(" años"), repCont);
  // aM y aP ---------------------------------------------------------------
  var latd = Math.round(DMS.Decode(lat).val);
  var lond = Math.round(DMS.Decode(lon).val);
  // Longitud Arco de Meridiano de 1"
  r = geod.Inverse(latd, lond, latd + 1 / 3600, lond);
  var am = r.s12;
  report("Arco de Meridiano de 1\" para lat=".concat(latd).concat(":"), repCont);
  report("\t".concat(am.toFixed(3)).concat(" m"), repCont);
  // Longitud Arco de Paralelo de 1"
  r = geod.Inverse(latd, lond, latd, lond + 1 / 3600);
  var ap = r.s12;
  report("Arco de Paralelo de 1\" para lat=".concat(latd).concat(":"), repCont);
  report("\t".concat(ap.toFixed(3)).concat(" m"), repCont);
  // e y n VEMOS2017 -------------------------------------------------------
  var latd = DMS.Decode(lat).val.toFixed(0);
  var lond = DMS.Decode(lon).val.toFixed(0);
  var nc = parseFloat(vms2017[lond][latd].n);
  var ec = parseFloat(vms2017[lond][latd].e);
  report("Componentes Norte y Este de velocidad según VEMOS2017:", repCont);
  report("\t".concat(nc).concat(" m/año"), repCont);
  report("\t".concat(ec).concat(" m/año"), repCont);
  // cLat y cLon -----------------------------------------------------------
  var cLat = nc / am * years;
  var cLon = ec / ap * years;
  var latC = DMS.Decode(lat).val;
  var lonC = DMS.Decode(lon).val;
  report("Corrección de Latitud y Longitud según VEMOS2017:", repCont)
  report("\t".concat(-nc*years).concat(" m").concat("\t").concat((-cLat).toFixed(5)).concat("\""), repCont);
  report("\t".concat(-ec*years).concat(" m").concat("\t").concat((-cLon).toFixed(5)).concat("\""), repCont);
  // Coordenadas corregidas a "reference date"
  latC = latC - cLat / 3600;
  lonC = lonC - cLon / 3600;
  // cLat y cLon velar2015a2007 desplazamiento FIJO ---------------------------
  cLat = parseFloat(velar2015a2007[lond][latd].n);
  cLon = parseFloat(velar2015a2007[lond][latd].e);
  latC = latC + cLat / am / 3600;
  lonC = lonC + cLon / ap / 3600;
  report("Corrección de Latitud y Longitud según\nVelAR 2015 a 2006.632:", repCont);
  report("\t".concat(cLat).concat(" m").concat("\t").concat((cLat/am).toFixed(5)).concat("\""), repCont);
  report("\t".concat(cLon).concat(" m").concat("\t").concat((cLon/ap).toFixed(5)).concat("\""), repCont);
  // Coordenadas corregidas a 2006.632
  latCdms = DMS.Encode(latC, DMS.SECOND, 5, DMS.LATITUDE);
  lonCdms = DMS.Encode(lonC, DMS.SECOND, 5, DMS.LONGITUDE);
  report("<strong>Latitud y Longitud POSGAR07, 2006.632:</strong>", repCont);
  report("\t<strong>".concat(latC).concat("\t").concat(latCdms).concat("</strong>"), repCont);
  report("\t<strong>".concat(lonC).concat("\t").concat(lonCdms).concat("</strong>"), repCont);
  // Distancia entre coordenadas ITRF y corregidas
  report("Distancia entre coordenadas ITRF y POSGAR07 [m]:", repCont);
  report("\t".concat(geod.Inverse(lat, lon, latC, lonC).s12.toFixed(3)), repCont);
  report("Azimut de segmento punto ITRF, punto POSGAR07 [º]:", repCont);
  report("\t".concat(geod.Inverse(lat, lon, latC, lonC).azi1.toFixed(3)), repCont);
  return [latC, lonC];
}

function getYearDay(date) {
  // takes "yyyy-mm-dd" strings as date
  // returns year day, ie. 1-feb is day 32
  var d = date.split("-");
  var dateTo = new Date(d[0], d[1] - 1, d[2]);
  var dateFrom = new Date(dateTo.getFullYear(), 0, 0);
  var diff = dateTo - dateFrom;
  var oneDay = 1000 * 60 * 60 * 24;
  return Math.floor(diff / oneDay);
}

function getYearFraction(date) {
  // takes "yyyy-mm-dd" strings as date
  // returns year and fraction, ie. 2006.632
  var d = date.split("-");
  var totalDays = getYearDay(d[0].concat('-12-31'));
  var days = getYearDay(date);
  return parseFloat(d[0]) + days / totalDays;
}

function report(event, rc) {
  // if no repCont, no log
  if (rc != null) {
    if (event == null) rc.innerHTML = '';
    else {
      console.log(event);
      rc.innerHTML = rc.innerHTML.concat(event).concat('\n');
    }
  }
}
