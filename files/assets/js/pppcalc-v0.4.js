// Zone Limits
var zoneLimitW  = -63.5;
var zoneLimitCW = -61.5;
var zoneLimitCE = -59 - 15/60.0;
var zoneLimitE  = -56 - 40/60.0;
var zoneLimitN  = -28.0;
var zoneLimitS  = -41.0;
// Time Limits
var yearLimitMin = 2012;
var yearLimitMax = 2017;
// Correction parameters
var oneYearCorrection = 0.00038;
var yearLimitMinCorrection = 0.00223;
// Global

// Error Codes/Messages
var VAL_OUT_OF_RANGE   = "El valor de los $1 de la $2 está fuera de rango.\nDebe ser un valor entre $3 y $4."
var LATD_OUT_OF_RANGE  = VAL_OUT_OF_RANGE.replace(/\$1/g,"GRADOS")
                                         .replace(/\$2/g,"LATITUD")
                                         .replace(/\$3/g,"-41")
                                         .replace(/\$4/g,"-28");
var LATM_OUT_OF_RANGE  = VAL_OUT_OF_RANGE.replace(/\$1/g,"MINUTOS")
                                         .replace(/\$2/g,"LATITUD")
                                         .replace(/\$3/g,"0")
                                         .replace(/\$4/g,"59");
var LATS_OUT_OF_RANGE  = VAL_OUT_OF_RANGE.replace(/\$1/g,"SEGUNDOS")
                                         .replace(/\$2/g,"LATITUD")
                                         .replace(/\$3/g,"0")
                                         .replace(/\$4/g,"59.999999");
var LOND_OUT_OF_RANGE  = VAL_OUT_OF_RANGE.replace(/\$1/g,"GRADOS")
                                         .replace(/\$2/g,"LONGITUD")
                                         .replace(/\$3/g,"-63")
                                         .replace(/\$4/g,"-56");
var LONM_OUT_OF_RANGE  = VAL_OUT_OF_RANGE.replace(/\$1/g,"MINUTOS")
                                         .replace(/\$2/g,"LONGITUD")
                                         .replace(/\$3/g,"0")
                                         .replace(/\$4/g,"59");
var LONS_OUT_OF_RANGE  = VAL_OUT_OF_RANGE.replace(/\$1/g,"SEGUNDOS")
                                         .replace(/\$2/g,"LONGITUD")
                                         .replace(/\$3/g,"0")
                                         .replace(/\$4/g,"59.999999");
var COORD_OUT_OF_RANGE = "La coordenada $1 está fuera de la Zona de validez.";
var LAT_OUT_OF_RANGE   = COORD_OUT_OF_RANGE.replace(/\$1/g,"LATITUD");
var LON_OUT_OF_RANGE   = COORD_OUT_OF_RANGE.replace(/\$1/g,"LONGITUD");
var YEAR_OUT_OF_RANGE  = "No están disponibles las correcciones para el año ingresado.\nDebe ser un valor entre $3 y $4."
var YEAR_OUT_OF_RANGE  = YEAR_OUT_OF_RANGE.replace(/\$3/g,yearLimitMin)
                                          .replace(/\$4/g,yearLimitMax);

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
  if (year >= yearLimitMin && year <= yearLimitMax) {
    var years = year - yearLimitMin;
    var correction = yearLimitMinCorrection + oneYearCorrection * years;
    return lat - (correction/3600);
  }
  else
    error(YEAR_OUT_OF_RANGE);
  return;
};

function validateData() {
  // Get Data ------------------------------------------------------------------
  // longitude
  var lond = parseInt(document.getElementById('lond').value);
  var lonm = parseInt(document.getElementById('lonm').value);
  var lons = parseFloat(document.getElementById('lons').value).toFixed(6);
  // latitude
  var latd = parseInt(document.getElementById('latd').value);
  var latm = parseInt(document.getElementById('latm').value);
  var lats = parseFloat(document.getElementById('lats').value).toFixed(6);
  // year
  var year = parseInt(document.getElementById('ano').value);
  // Set Data Validated --------------------------------------------------------
  // longitude
  document.getElementById('lond').value = isNaN(lond) ? 0 : lond;
  document.getElementById('lonm').value = isNaN(lonm) ? 0 : lonm;
  document.getElementById('lons').value = isNaN(lons) ? 0 : lons;
  // latitude
  document.getElementById('latd').value = isNaN(latd) ? 0 : latd;
  document.getElementById('latm').value = isNaN(latm) ? 0 : latm;
  document.getElementById('lats').value = isNaN(lats) ? 0 : lats;
  // year
  document.getElementById('ano').value  = isNaN(year) ? 0 : year;
};

function pppcalc() {
  // Validate Data -------------------------------------------------------------
  validateData();
  // Get Data ------------------------------------------------------------------
  // latitude
  var latd = parseInt(document.getElementById('latd').value);
  var latm = parseInt(document.getElementById('latm').value);
  var lats = parseFloat(document.getElementById('lats').value);
  var lat  = latd - latm/60.0 - lats/3600.0;
  // longitude
  var lond = parseInt(document.getElementById('lond').value);
  var lonm = parseInt(document.getElementById('lonm').value);
  var lons = parseFloat(document.getElementById('lons').value);
  var lon  = lond - lonm/60.0 - lons/3600.0;
  // year
  var year = parseInt(document.getElementById('ano').value);
  // Verify Data ---------------------------------------------------------------
  // proper lon/lat min/sec
  if(latd < -41 || latd > -28) { error(LATD_OUT_OF_RANGE); return; };
  if(latm < 0 || latm >  59  ) { error(LATM_OUT_OF_RANGE); return; };
  if(lats < 0 || lats >= 60.0) { error(LATS_OUT_OF_RANGE); return; };
  if(lond < -63 || lond > -56) { error(LOND_OUT_OF_RANGE); return; };
  if(lonm < 0 || lonm >  59  ) { error(LONM_OUT_OF_RANGE); return; };
  if(lons < 0 || lons >= 60.0) { error(LONS_OUT_OF_RANGE); return; };
  // zone limits
  var faja = validateFaja(lon);
  if (!validateLatLimits(lat)) { error(LAT_OUT_OF_RANGE); return; };
  if (faja == 0) { error(LON_OUT_OF_RANGE); return; };
  if (!validateYearLimits(year)) { error(YEAR_OUT_OF_RANGE); return; };
  // Correct coordinates -------------------------------------------------------
  // latitude
  lat   = correctLat(lat, year);
  latd  = Math.ceil(lat);
  latm_ = (lat - latd) * 60.0;
  latm  = Math.abs(Math.ceil(latm_));
  lats  = Math.abs(parseInt(((latm_ + latm) * 60.0) * 1000000) / 1000000);
  // longitude
  lon   = correctLon(lon, faja);
  lond  = Math.ceil(lon);
  lonm_ = (lon - lond) * 60.0;
  lonm  = Math.abs(Math.ceil(lonm_));
  lons  = Math.abs(parseInt(((lonm_ + lonm) * 60.0) * 1000000) / 1000000);
  // Show results --------------------------------------------------------------
  // latitude
  document.getElementById('latdc').value = latd;
  document.getElementById('latmc').value = latm;
  document.getElementById('latsc').value = lats.toFixed(6);
  // longitude
  document.getElementById('londc').value = lond;
  document.getElementById('lonmc').value = lonm;
  document.getElementById('lonsc').value = lons.toFixed(6);
};
