.. title: Servicio PPP
.. slug: result
.. date: 24/03/2020 18:00:00 UTC-03:00
.. tags: mathjax, ppp
.. link:
.. description: PPP
.. type: text

<h2>Resultados del procesamiento</h2>

<?php
require 'env.php';

function printer($array)
{ // print array wihout key
  foreach ($array as $key => $value) {
    echo $value . "\n";
  }
};

function execute($command, $show = true)
{ // execute command, and optionally show output
  exec($command, $output, $retval);
  if ($show) {
    if ($retval)
      echo 'exec:<pre>' . $command . "</pre>";
    echo '<pre>';
    printer($output);
    if ($retval)
      echo '<span class="text-danger">Error durante el procesamiento.</span>';
    echo "</pre>";
    if ($retval)
      echo 'retval:<pre>' . $retval . "</pre>";
  };
  return $retval;
};
function executePostPPP($command)
{
  echo '<div id="output" class="col-lg-8">';
  $retval = execute($command);
  echo '</div>';
  echo '<div id="map" class=" map col-lg-4" style="height: 362px"></div>
        <div id="popup" class="ol-popup">
          <a href="#" id="popup-closer" class="ol-popup-closer"></a>
          <div id="popup-content"></div>
        </div>';
  return $retval;
};
// verify CAPTCHA, ignore if localhost
if (isset($_POST['recaptcha_response'])) {

  // Build POST request:
  $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
  $recaptcha_response = $_POST['recaptcha_response'];

  // Make and decode POST request:
  $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
  $recaptcha = json_decode($recaptcha);

  // Take action based on the score returned:
  if ($recaptcha->success == true && $recaptcha->score >= 0.5) {
    $captchaOk = true;
  } else {
    echo '<h2 class="text-danger">CAPTCHA no superado... ¿es usted un robot?</h2>';
  }
}
// Validation and process
// Enter if exists _POST and CAPTCHA is ok
if (!empty($_POST) && $captchaOk) {
  // IDW Parameters
  $idw_n = $_POST["idw_n"];
  $idw_p = $_POST["idw_p"];
  // Known coordinates
  $knownCoord = $_POST["coordinates"];
  if ($knownCoord) {
    $c = explode(",", trim($knownCoord));
    $lat = explode(" ", trim($c[0]));
    $lon = explode(" ", trim($c[1]));
    $latComp = trim($lat[0]) . ' ' . trim($lat[1]) . ' ' . trim($lat[2]);
    $lonComp = trim($lon[0]) . ' ' . trim($lon[1]) . ' ' . trim($lon[2]);
  }
  // RINEX File
  $tmpFile = $_FILES["rinex"]["tmp_name"];
  $rinexFileName = basename($_FILES["rinex"]["name"]);
  // targets
  $targetDir = "uploads/";
  $targetFile = $targetDir . $rinexFileName;
  // RINEX properties
  $rinexFileType = pathinfo($targetFile, PATHINFO_EXTENSION);
  $rinexFileBase = pathinfo($targetFile, PATHINFO_FILENAME);
  $rinexFileSize = round($_FILES["rinex"]["size"] / 1024, 3); // file size in KB
  $rinexMaxSize = 5 * 1024; // 10240 KB = 5 MB
  // Results dir
  $pppResultDir = "results_ppp/";
  $pppSummary = $pppResultDir . pathinfo($rinexFileBase, PATHINFO_FILENAME) . ".sum";
  // bash commands to execute with Python interpreter
  // COMMAND: send to CSRS-PPP
  $cmdCSRS = "python3 csrs_ppp_auto.py --user_name santiagonob@gmail.com --ref ITRF --rnx " . $targetFile . " --results_dir ../" . $pppResultDir;
  // COMMAND: transform ITRF->POSGAR
  $cmdPostPPP = 'python3 post_ppp.py ' . $pppSummary;
  if (!$idw_n || ($idw_n < 0 || $idw_n > 100))
    $cmdPostPPP = $cmdPostPPP . ' 3';
  else
    $cmdPostPPP = $cmdPostPPP . ' ' . $idw_n;
  if (!$idw_p || ($idw_n < 1 || $idw_n > 20))
    $cmdPostPPP = $cmdPostPPP . ' 1';
  else
    $cmdPostPPP = $cmdPostPPP . ' ' . $idw_p;
  if ($knownCoord)
    $cmdPostPPP = $cmdPostPPP . ' "' . $latComp . '" "' . $lonComp . '"';
  // FLAGS: Upload and Process
  $formOk = true;       // Assume Form is OK
  $processedOk = false; // Assume RINEX never processed before
  $errors = "";         // Assume no errors
  // Check if file already exists, it means RINEX already sent to CSRS-PPP
  if (file_exists($pppSummary)) {
    echo '<div class="col-lg-12">
          <p>El mismo archivo ya había sido procesado por CSRS-PPP, por lo
             tanto, se utilizan resultados obtenidos previamente para minimizar
             los tiempos de espera.</p>
          <p>Si desea volver a procesar, puede hacerlo modificando el nombre
             del archivo RINEX.</p>
          </div>';
    // set FLAGS
    $formOk = true;
    $processedOk = true;
  }
  // Check if file exists, must attach a RINEX file
  if (!$rinexFileName) {
    $errors .= '<p>Debe adjuntar un archivo de observaciones.</p>';
    $formOk = false;
  }
  // Check file size, must be smaller than rinexMaxSize
  if ($rinexFileSize > $rinexMaxSize) {
    $errors .= '<p>El archivo es demasiado extenso. El tamaño máximo permitido es ' . $rinexMaxSize . ' KB.
                Para comprimir:
                <a href= "https://terras.gsi.go.jp/ja/crx2rnx.html">CompactRINEX (Hatanaka)</a> (formato <code>yyd</code>),
                <a href="https://www.7-zip.org/">7zip</a> (formato <code>zip</code> o <code>gz</code>).</p>';
    $formOk = false;
  }
  // Allow certain compressed file formats
  if ($rinexFileType != "zip" && $rinexFileType != "gz" && $rinexFileType != "Z") {
    $errors .= '<p>Solo se aceptan archivos RINEX comprimidos <code>zip</code>, <code>gz</code> o <code>Z</code>.</p>';
    $formOk = false;
  }
  // PROCESS...
  if ($formOk) {
    if ($processedOk)
      executePostPPP($cmdPostPPP);
    else {
      if (move_uploaded_file($tmpFile, $targetFile)) {
        echo "<p>El archivo <code>" . $rinexFileName . "</code> (<code>" . $rinexFileSize . " KB</code>) fue cargado correctamente.</p>";
        if (!execute($cmdCSRS, false))
          executePostPPP($cmdPostPPP);
      } else // ERROR loading file
        echo '<h3 class="text-danger">Hubo algún problema durante la carga del archivo</h3>
              <p class="text-danger">Por favor vuelva a intentarlo.</p>';
    }
  } else // ERROR in form
    echo '<h3 class="text-danger">Hubo algún error en los datos.</h3>' . $errors . '
          <p class="text-danger">Por favor vuelva a intentarlo.</p>';
} else {
  echo '<h2 class="text-danger">CAPTCHA no superado... ¿es usted un robot?</h2>';
}
?>

<ul class="pager col-lg-12">
  <li class="previous"><a href="../">&larr; Volver</a></li>
</ul>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.2.1/css/ol.css">
<script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.2.1/build/ol.js"></script>
<!--
  <link rel="stylesheet" href="css/map.css" type="text/css">
  <script type="text/javascript" src="js/map.js"></script>
-->

<style>
  @import url(https://fonts.googleapis.com/css?family=Oswald);

  .ol-popup {
    position: absolute;
    background-color: white;
    -webkit-filter: drop-shadow(0 1px 4px rgba(0, 0, 0, 0.2));
    filter: drop-shadow(0 1px 4px rgba(0, 0, 0, 0.2));
    padding: 15px;
    border-radius: 10px;
    border: 1px solid #cccccc;
    bottom: 12px;
    left: -50px;
    min-width: 200px;
  }

  .ol-popup:after,
  .ol-popup:before {
    top: 100%;
    border: solid transparent;
    content: " ";
    height: 0;
    width: 0;
    position: absolute;
    pointer-events: none;
  }

  .ol-popup:after {
    border-top-color: white;
    border-width: 10px;
    left: 48px;
    margin-left: -10px;
  }

  .ol-popup:before {
    border-top-color: #cccccc;
    border-width: 11px;
    left: 48px;
    margin-left: -11px;
  }

  .ol-popup-closer {
    text-decoration: none;
    position: absolute;
    top: 2px;
    right: 8px;
  }

  .ol-popup-closer:after {
    content: "✖";
  }
</style>

<script>
  // Styles ---------------------------------------------------------------------
  var image = function(feature) {
    return new ol.style.Circle({
      radius: 4,
      fill: new ol.style.Fill({
        color: feature.get('color')
      }),
      stroke: new ol.style.Stroke({
        color: 'black',
        width: 2
      }),
    })
  };

  var text = function(feature) {
    return new ol.style.Text({
      textAlign: 'left', // feature.get('align'),
      textBaseline: 'bottom', // feature.get('bline'),
      font: 'bold 11px Oswald',
      text: feature.get('name'),
      fill: new ol.style.Fill({
        color: 'black', // feature.get('color')
      }),
      stroke: new ol.style.Stroke({
        color: 'white',
        width: 3
      }),
    })
  };

  var styles = function(feature) {
    return {
      'Point': new ol.style.Style({
        image: image(feature),
        text: text(feature)
      }),
      'LineString': new ol.style.Style({
        stroke: new ol.style.Stroke({
          color: feature.get('color'),
          text: text(feature),
          width: 4
        })
      }),
    }
  };

  var styleFunction = function(feature) {
    return styles(feature)[feature.getGeometry().getType()];
  };

  // vectorPoints ---------------------------------------------------------------
  var vectorPointsIDW = new ol.layer.Vector({
    title: 'IDW',
    source: new ol.source.Vector({
      url: 'idw.geojson',
      format: new ol.format.GeoJSON()
    }),
    style: styleFunction
  });

  // Map ------------------------------------------------------------------------
  var map = new ol.Map({
    target: 'map',
    controls: ol.control.defaults().extend([
      new ol.control.ScaleLine()
    ]),
    layers: [
      new ol.layer.Tile({
        source: new ol.source.OSM()
      }),
      vectorPointsIDW,
      new ol.layer.Vector()
    ],
    view: new ol.View({
      center: ol.proj.transform([-60.628, -32.959], 'EPSG:4326', 'EPSG:3857'),
      zoom: 4
    })
  });

  function resetMap() {
    vectorLayer = map.getLayers().getArray()[1];
    vectorLayer = vectorPointsIDW;
    map.getView().setCenter(ol.proj.fromLonLat([-60.628, -32.959]));
    map.getView().setZoom(4);
  }

  // Popup ----------------------------------------------------------------------

  // Elements that make up the popup.
  var container = document.getElementById('popup');
  var content = document.getElementById('popup-content');
  var closer = document.getElementById('popup-closer');

  // Create an overlay to anchor the popup to the map.
  var popup = new ol.Overlay({
    element: container,
    autoPan: true,
    autoPanAnimation: {
      duration: 250
    }
  });
  map.addOverlay(popup);

  map.on('singleclick', function(evt) {
    var feature = map.forEachFeatureAtPixel(evt.pixel,
      function(feature, layer) {
        return feature;
      });
    if (feature) {
      var coord = feature.getGeometry().getCoordinates();
      if (feature.getGeometry().getType() == 'LineString')
        coord = coord[1];
      var desc = feature.get('description');
      content.innerHTML = '<div style="font-size:small"><span style="font-weight:bold;font-size:20px;color:blue">' + feature.get('name') + '</span><br>' + desc + '</div>';
      popup.setPosition(coord);
    }
  });

  /**
   * Add a click handler to hide the popup.
   * @return {boolean} Don't follow the href.
   */
  closer.onclick = function() {
    popup.setPosition(undefined);
    closer.blur();
    return false;
  };

  // Fit Map to VectorSource
  var vectorSource = vectorPointsIDW.getSource();
  vectorSource.once('change', function(evt) {
    if (vectorSource.getState() === 'ready') {
      // now the source is fully loaded
      if (vectorSource.getFeatures().length > 0) {
        map.getView().fit(vectorSource.getExtent(), map.getSize());
        map.getView().setZoom(map.getView().getZoom() - .5);
        // console.info(map.getView().getCenter());
        // console.info(map.getView().getZoom());
      }
    }
  });
</script>