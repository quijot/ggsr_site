// functions showReport() / hideReport() / fillReportTag(tag)

var reportSkel = `
Informe Calculadora PPP
=======================

ID sitio: site
Sesión:   duration hs

Coordenadas PPP
---------------
Latitud:  latdº latm' lats"
Longitud: londº lonm' lons"
Año de medición: ano

Coordenadas POSGAR 2007 (época 2006.632)
-----------------------
Latitud:  latdcº latmc' latsc"
Longitud: londcº lonmc' lonsc"`

function hideReport() {
  document.getElementById('informe').style.display = 'none';
};

function showReport() {
  var report = document.getElementById('informe-content');
  report.innerHTML = reportSkel;
  var inputs = document.getElementsByTagName("input");
  for (var i=0; i < inputs.length; i++) {
    var tag = inputs[i].getAttribute('id');
    report.innerHTML = report.innerHTML.replace(tag, document.getElementById(tag).value);
  }
  document.getElementById('informe').style.display = 'block';
};
