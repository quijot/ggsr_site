// Styles ---------------------------------------------------------------------
var image = function (feature) {
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

var text = function (feature) {
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

var styles = function (feature) {
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

var styleFunction = function (feature) {
  return styles(feature)[feature.getGeometry().getType()];
};

// vectorPoints ---------------------------------------------------------------
var vectorPointsRAMSAC = new ol.layer.Vector({
  title: 'RAMSAC',
  source: new ol.source.Vector({
    url: 'js/ramsac.geojson',
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
    vectorPointsRAMSAC,
    new ol.layer.Vector()
  ],
  view: new ol.View({
    center: ol.proj.transform([-60.628, -32.959], 'EPSG:4326', 'EPSG:3857'),
    zoom: 4
  })
});

function resetMap() {
  vectorLayer = map.getLayers().getArray()[1];
  vectorLayer = vectorPointsRAMSAC;
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

map.on('singleclick', function (evt) {
  var feature = map.forEachFeatureAtPixel(evt.pixel,
    function (feature, layer) {
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
closer.onclick = function () {
  popup.setPosition(undefined);
  closer.blur();
  return false;
};

// Fit Map to VectorSource
var vectorSource = vectorPointsRAMSAC.getSource();
vectorSource.once('change', function (evt) {
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