.. title: ITRF→POSGAR
.. slug: itrf2posgar
.. date: 2020-04-04 04:04:04 UTC-03:00
.. tags:
.. category:
.. link:
.. description: itrf2posgar calculations
.. type: text

<div class="well col-lg-12">
  <form class="form-horizontal" id="form" method="get" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
    <fieldset id="form-fieldset">
      <legend>Transfrmación <strong>ITRF→POSGAR</strong></legend>
      <div class="form-group">
        <!-- Coordenadas ITRF -->
        <label for="itrf_coordinates" class="col-lg-2 control-label">Coordenadas ITRF</label>
        <div class="col-lg-10">
          <input type="text" class="form-control" id="itrf_coordinates" name="itrf_coordinates" placeholder="-32.95935194,-60.62842564" value="<?php echo htmlentities($_GET["itrf_coordinates"]); ?>">
        </div>
      </div>
      <div class="form-group">
        <!-- Fecha de medición -->
        <label for="obs_date" class="col-lg-2 control-label">Fecha de medición</label>
        <div class="col-lg-4">
          <input type="date" class="form-control" id="obs_date" name="obs_date" placeholder="2016-08-09" value="<?php echo htmlentities($_GET["obs_date"]); ?>">
        </div>
      </div>
      <div class="form-group">
        <!-- MESSAGE -->
        <div class="col-lg-7 col-lg-offset-2">
          <div id="wait-msg" style="display: none">
            <p>
              <strong class="text-warning">por favor no cierre el navegador</strong>.
            </p>
          </div>
        </div>
        <!-- SUBMIT -->
        <div class="col-lg-1">
          <div id="sending" style="display:none; text-align:center"><img src="loading.svg"></div>
        </div>
        <div class="col-lg-2">
          <button type="submit" class="btn btn-primary pull-right" id="submit" name="submit">Enviar</button>
        </div>
      </div>
      <legend>Opcionales</legend>
      <div class="form-group">
        <!-- Coordinates (optional) -->
        <label for="posgar_coordinates" class="col-lg-2 control-label">Conocidas</label>
        <div class="col-lg-4">
          <input type="text" class="form-control" id="posgar_coordinates" name="posgar_coordinates" placeholder="-31 14 51.30585, -61 28 16.28025" value="<?php echo htmlentities($_GET["posgar_coordinates"]); ?>">
          <span class="help-block">Coordenadas POSGAR07 para comparar</span>
        </div>
        <!-- IDW parameters (default n=3, p=1) -->
        <label for="idw_n" class="col-lg-1 control-label">IDW (n)</label>
        <div class="col-lg-2">
          <input type="number" class="form-control" id="idw_n" name="idw_n" placeholder="3" min=1 max=100 disabled>
          <span class="help-block">[<code>1-100</code>] (Default: <code>3</code>) Cantidad de EP para
            interpolar</span>
        </div>
        <label for="idw_p" class="col-lg-1 control-label">IDW (p)</label>
        <div class="col-lg-2">
          <input type="number" class="form-control" id="idw_p" name="idw_p" placeholder="1" min=1 max=20 disabled>
          <span class="help-block">[<code>1-20</code>] (Default: <code>1</code>) Ponderación de la distancia</span>
        </div>
      </div>
    </fieldset>
  </form>
</div>

<?php
if (!empty($_GET['itrf_coordinates']) and !empty($_GET['obs_date'])) {
  // RESULTS

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
      echo '<pre id="' . $output_id . '">';
      printer($output);
      if ($retval)
        echo '<span class="text-danger">Error durante el procesamiento.</span>';
      echo "</pre>";
      if ($retval)
        echo 'retval:<pre>' . $retval . "</pre>";
    };
    return $retval;
  };

  function itrf2posgar($lat, $lon, $obs_date, $n, $p, $lat_comp = null, $lon_comp = null)
  {
    // Prepare command
    $cmd = 'python3 calc.py ' . $lat . ' ' . $lon . ' "' . $obs_date . '"';
    // Validate parameters
    $cmd .= (!$n or ($n < 0 or $n > 100)) ? ' 3' : ' ' . $n;
    $cmd .= (!$p or ($p < 1 or $p > 20)) ? ' 1' : ' ' . $p;
    $cmd .= (isset($lat_comp) and isset($lon_comp)) ? ' "' . $lat_comp . '" "' . $lon_comp . '"' : '';
    echo '<div id="output" class="col-lg-8">';
    $retval = execute($cmd, 'result');
    echo '</div>';
    echo '<div id="map" class=" map col-lg-4" style="height: 362px"></div>
        <div id="popup" class="ol-popup">
          <a href="#" id="popup-closer" class="ol-popup-closer"></a>
          <div id="popup-content"></div>
        </div>';
    return $retval;
  };

  // Validation and process
  // Handle errors
  try {
    // ITRF->POSGAR
    // ITRF Coordinates
    $c = explode(',', trim($_GET["itrf_coordinates"]));
    $lat = trim($c[0]);
    $lon = trim($c[1]);
    // Obs Date
    $obs_date = $_GET["obs_date"];
    // IDW Parameters
    $idw_n = $_GET["idw_n"];
    $idw_p = $_GET["idw_p"];
    // Known coordinates
    $lat_comp = $lon_comp = null;
    if ($coord = $_GET["posgar_coordinates"]) {
      $c = explode(',', trim($coord));
      $_lat = explode(' ', trim($c[0]));
      $_lon = explode(' ', trim($c[1]));
      $lat_comp = trim($_lat[0]) . ' ' . trim($_lat[1]) . ' ' . trim($_lat[2]);
      $lon_comp = trim($_lon[0]) . ' ' . trim($_lon[1]) . ' ' . trim($_lon[2]);
    }
    itrf2posgar($lat, $lon, $obs_date, $idw_n, $idw_p, $lat_comp, $lon_comp);
  } catch (RuntimeException $e) {
    echo '<h3>Hubo algún problema</h3><p class="text-danger">' . $e->getMessage() . '</p>';
  }
}
?>

<!-- Map by OpenLayers -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.2.1/css/ol.css">
<script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.2.1/build/ol.js"></script>

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