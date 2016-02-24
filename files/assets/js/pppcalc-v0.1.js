// Zone Limits
var zoneLimitW  = -63.5;
var zoneLimitCW = -61.5;
var zoneLimitCE = -59 - 15/60.0;
var zoneLimitE  = -56 - 40/60.0;
var zoneLimitN  = -28.0;
var zoneLimitS  = -41.0;
// Time Limits
var yearLimitMin = 2012;
var yearLimitMax = 2015;
// Error Codes
var LON_OUT_OF_RANGE  = "La coordenada Longitud está fuera de la Zona de validez."
var LAT_OUT_OF_RANGE  = "La coordenada Latitud está fuera de la Zona de validez."
var YEAR_OUT_OF_RANGE = "No están disponibles las correcciones para el año ingresado."

function error(errorMsg) {
  alert(errorMsg);
};

function validateFaja(lon) {
  if (lon <= zoneLimitE && lon >= zoneLimitCE)
    return 1;
  else if (lon < zoneLimitCE && lon >= zoneLimitCW)
    return 2;
  else if (lon < zoneLimitCW && lon >= zoneLimitW)
    return 3;
  else
    return 0; //outOfRange
};

function validateLatLimits(lat) {
  return (lat <= zoneLimitN && lat >= zoneLimitS);
};

function validateYearLimits(year) {
  return (year >= yearLimitMin && year <= yearLimitMax);
};

function correctLon(lon, faja) {
  switch (faja) {
    case 1:
      return lon + (0.001/3600);
    case 2:
      return lon + (0.002/3600);
    case 3:
      return lon + (0.003/3600);
    default:
      error(LON_OUT_OF_RANGE);
      return;
  };
};

function correctLat(lat, year) {
  switch (year) {
    case 2012:
      return lat - (0.00223/3600);
    case 2013:
      return lat - (0.00261/3600);
    case 2014:
      return lat - (0.00299/3600);
    case 2015:
      return lat - (0.00337/3600);
    default:
      error(YEAR_OUT_OF_RANGE);
      return;
  };
};

function pppcalc() {
  // Get Data -----------------------------------------------------------------
  // longitud
  var lond = parseInt(document.getElementById('lond').value);   lond = isNaN(lond) ? 0 : lond;
  var lonm = parseInt(document.getElementById('lonm').value);   lonm = isNaN(lonm) ? 0 : lonm;
  var lons = parseFloat(document.getElementById('lons').value); lons = isNaN(lons) ? 0 : lons;
  var lon = lond - lonm/60.0 - lons/3600.0;
  // longitud
  var latd = parseInt(document.getElementById('latd').value);   latd = isNaN(latd) ? 0 : latd;
  var latm = parseInt(document.getElementById('latm').value);   latm = isNaN(latm) ? 0 : latm;
  var lats = parseFloat(document.getElementById('lats').value); lats = isNaN(lats) ? 0 : lats;
  var lat = latd - latm/60.0 - lats/3600.0;
  // year
  var year = parseInt(document.getElementById('ano').value);    year = isNaN(year) ? 0 : year;
  // Validate Data ------------------------------------------------------------
  var faja = validateFaja(lon);
  if (faja == 0) {
    error(LON_OUT_OF_RANGE);
    return;
  };
  if (!validateLatLimits(lat)) {
    error(LAT_OUT_OF_RANGE);
    return;
  };
  if (!validateYearLimits(year)) {
    error(YEAR_OUT_OF_RANGE);
    return;
  };
  // Correct coordinates ------------------------------------------------------
  lon = correctLon(lon, faja);
  lond = Math.ceil(lon);
  lonm_ = (lon - lond) * 60.0;
  lonm = Math.abs(Math.ceil(lonm_));
  lons = Math.abs(parseInt(((lonm_ + lonm) * 60.0) * 1000000) / 1000000);
  document.getElementById('londc').value = lond;
  document.getElementById('lonmc').value = lonm;
  document.getElementById('lonsc').value = lons;
  lat = correctLat(lat, year);
  latd = Math.ceil(lat);
  latm_ = (lat - latd) * 60.0;
  latm = Math.abs(Math.ceil(latm_));
  lats = Math.abs(parseInt(((latm_ + latm) * 60.0) * 1000000) / 1000000);
  document.getElementById('latdc').value = latd;
  document.getElementById('latmc').value = latm;
  document.getElementById('latsc').value = lats;
};

function generarInforme() {
  alert("Esta función se encuentra en desarrollo.");
};
