.. title: Servicio PPP
.. slug: result
.. date: 24/03/2020 18:00:00 UTC-03:00
.. tags: mathjax, ppp
.. link:
.. description: PPP
.. type: text

<h2>Resultados del procesamiento</h2>

<?php

function printer($array)
{ // print array wihout key
  foreach ($array as $key => $value) {
    echo $value . "\n";
  }
};

function execute($command, $output_id = '', $show = true)
{ // execute command, and optionally show output
  exec($command, $output, $retval);
  if ($show) {
    if ($retval)
      echo 'exec:<pre>' . $command . "</pre>";
    echo '<pre id="' . $output_id . '" class="mx-auto">';
    printer($output);
    if ($retval)
      echo '<span class="text-danger">Error durante el procesamiento.</span>';
    echo "</pre>";
    if ($retval)
      echo 'retval:<pre>' . $retval . "</pre>";
  };
  return $retval;
};

function csrs_ppp($user, $rinex_path, $result_dir = '')
{
  $result_dir_param = ' --results_dir ../' . $result_dir;
  $cmd = 'python3 csrs_ppp_auto.py --user_name ' . $user . ' --ref ITRF --output_pdf lite --rnx ' . $rinex_path . $result_dir_param;
  return execute($cmd, '', false);
}

function itrf2posgar($sum_file, $n, $p, $lat = null, $lon = null)
{
  // Prepare command
  $cmd = 'python3 post_ppp.py ' . $sum_file;
  // Validate parameters
  $cmd .= (!$n or ($n < 0 or $n > 100)) ? ' 3' : ' ' . $n;
  $cmd .= (!$p or ($p < 1 or $p > 20)) ? ' 1' : ' ' . $p;
  $cmd .= (isset($lat) and isset($lon)) ? ' "' . $lat . '" "' . $lon . '"' : '';
  echo '<div id="output" class="col">';
  $retval = execute($cmd, 'result');
  echo '</div>';
  echo '<div id="map" class="map col mb-4" style="height: 362px"></div>
        <div id="popup" class="ol-popup">
          <a href="#" id="popup-closer" class="ol-popup-closer"></a>
          <div id="popup-content"></div>
        </div>
        </div>';
  return $retval;
};

function is_localhost()
{
  $whitelist = array(
    '127.0.0.1',
    '::1'
  );
  return in_array($_SERVER['REMOTE_ADDR'], $whitelist);
}

function verify_captcha()
{
  // verify CAPTCHA, ignore if localhost
  if (isset($_POST['recaptcha_response'])) {
    // Build POST request:
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    include 'secret.php'; // $recaptcha_secret
    $recaptcha_response = $_POST['recaptcha_response'];
    // Make and decode POST request:
    $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
    $recaptcha = json_decode($recaptcha);
    // Take action based on the score returned:
    return ($recaptcha->success == true and $recaptcha->score >= 0.5);
  }
  return false;
}

// Validation and process

$MAX_SIZE = 5 * 1024 * 1024; // 5 MB
$VALID_TYPES = array(
  'application/gzip',
  'application/x-gzip',
  'application/zip',
  'application/x-compress',
);

// Upload file and handle errors
try {

  if (!(verify_captcha() or is_localhost()))
    throw new RuntimeException('CAPTCHA no superado.');

  // Undefined | Multiple Files | $_FILES Corruption Attack
  // If this request falls under any of them, treat it invalid.
  if (
    !isset($_FILES['file']['error']) or
    is_array($_FILES['file']['error'])
  )
    throw new RuntimeException('Parámetros no válidos en la carga del archivo.');

  // Check $_FILES['upfile']['error'] value.
  switch ($_FILES['file']['error']) {
    case UPLOAD_ERR_OK:
      break;
    case UPLOAD_ERR_NO_FILE:
      throw new RuntimeException('Archivo no adjunto.');
    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
      throw new RuntimeException('Tamaño de archivo excedido.');
    default:
      throw new RuntimeException('Error desconocido.');
  }

  // You should also check filesize here.
  if ($_FILES['file']['size'] > $MAX_SIZE)
    throw new RuntimeException('Tamaño de archivo excedido.');

  // DO NOT TRUST $_FILES['file']['type'] VALUE !!
  // Check MIME Type by yourself.
  $file_type = mime_content_type($_FILES['file']['tmp_name']);
  if (!in_array($file_type, $VALID_TYPES))
    throw new RuntimeException('Tipo de archivo no válido.');

  // You should name it uniquely.
  // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
  // On this example, obtain safe unique name from its binary data.
  $target_dir = 'uploads/';
  $file_name = basename($_FILES['file']['name']);
  $source_path = $_FILES['file']['tmp_name'];
  $target_path = $target_dir . $file_name;
  if (file_exists($target_path))
    $output = 'Archivo existente: ';
  elseif (move_uploaded_file($source_path, $target_path))
    $output = 'Archivo cargado con éxito: ';
  else
    throw new RuntimeException('Fallo en la copia del archivo.');
  echo '<div class="col"><p>' . $output . $file_name . '.</p></div>';

  // Continue with process

  // Summary file exists? Means already processed by CSRS-PPP
  $file_base = pathinfo(pathinfo($file_name, PATHINFO_FILENAME), PATHINFO_FILENAME);
  $result_dir = 'results_ppp/';
  $summary = $result_dir . $file_base . '.sum';
  if (!file_exists($summary)) {
    // CSRS-PPP if not already done
    if (csrs_ppp('ggsr@fceia.unr.edu.ar', $target_path, $result_dir)) {
      throw new RuntimeException('Fallo en el envío/procesamiento del servicio CSRS-PPP.');
    }
  } else
    echo '<div class="col">
            <div class="alert alert-dismissible alert-info">
              <button type="button" class="close" data-dismiss="alert">&times;</button>
              <strong>¡Atención!</strong> Este archivo fue procesado recientemente
              por CSRS-PPP. Se utilizan los resultados obtenidos previamente para
              minimizar los tiempos de espera. Si desea volverlo a procesar, puede
              hacerlo modificando el nombre del archivo RINEX antes de cargarlo.
            </div>
          </div>';

  // ITRF->POSGAR
  // IDW Parameters
  $idw_n = $_POST["idw_n"];
  $idw_p = $_POST["idw_p"];
  // Known coordinates
  $lat_comp = $lon_comp = null;
  if ($coord = $_POST["coordinates"]) {
    $c = explode(',', trim($coord));
    $lat = explode(' ', trim($c[0]));
    $lon = explode(' ', trim($c[1]));
    $lat_comp = trim($lat[0]) . ' ' . trim($lat[1]) . ' ' . trim($lat[2]);
    $lon_comp = trim($lon[0]) . ' ' . trim($lon[1]) . ' ' . trim($lon[2]);
  }
  itrf2posgar($summary, $idw_n, $idw_p, $lat_comp, $lon_comp);
} catch (RuntimeException $e) {
  echo '<h3>Hubo algún problema</h3><p class="text-danger">' . $e->getMessage() . '</p>';
}
?>

<div class="col">
  <ul class="pager">
    <li class="previous"><a href="../">&larr; Volver</a></li>
  </ul>
</div>

<!-- Map by OpenLayers -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/css/ol.css">
<script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/build/ol.js"></script>
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
