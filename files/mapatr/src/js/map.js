// Points ----------------------------------------------------------------------
var createPointStyleFunction = function() {
  return function(feature) {
    var style = new ol.style.Style({
      image: new ol.style.Circle({
        radius: 5,
        fill: new ol.style.Fill({color: feature.get('color')}),
        stroke: new ol.style.Stroke({color: 'rgba(0, 0, 0, 0.3)', width: 2})
      }),
      text: new ol.style.Text({
        textAlign: 'left',
        textBaseline: 'baseline',
        font: 'bold 12px Oswald',
        text: feature.get('name'),
        fill: new ol.style.Fill({color: feature.get('color')}),
        stroke: new ol.style.Stroke({color: 'white', width: 3}),
      })
    });
    return [style];
  };
};

// vectorPoints
var vectorPointsBASE = new ol.layer.Vector({
  title: 'BASE',
  source: new ol.source.Vector({
    url: 'BASE.geojson',
    format: new ol.format.GeoJSON()
  }),
  style: createPointStyleFunction()
});
var vectorPointsUNR = new ol.layer.Vector({
  title: 'SIRGAS (Exp)',
  source: new ol.source.Vector({
    url: 'SIRGAS.geojson',
    format: new ol.format.GeoJSON()
  }),
  style: createPointStyleFunction()
});
var vectorPointsIGN = new ol.layer.Vector({
  title: 'RAMSAC-NTRIP (Ar)',
  source: new ol.source.Vector({
    url: 'IGN.geojson',
    format: new ol.format.GeoJSON()
  }),
  style: createPointStyleFunction()
});
var vectorPointsIGS = new ol.layer.Vector({
  title: 'IGS-RT',
  source: new ol.source.Vector({
    url: 'IGS.geojson',
    format: new ol.format.GeoJSON()
  }),
  style: createPointStyleFunction()
});
var vectorPointsIBGE = new ol.layer.Vector({
  title: 'IBGE-IP (Br)',
  source: new ol.source.Vector({
    url: 'IBGE.geojson',
    format: new ol.format.GeoJSON()
  }),
  style: createPointStyleFunction()
});
var vectorPointsREGNA_ROU = new ol.layer.Vector({
  title: 'REGNA-SGM (Uy)',
  source: new ol.source.Vector({
    url: 'REGNA.geojson',
    format: new ol.format.GeoJSON()
  }),
  style: createPointStyleFunction()
});

// Map -------------------------------------------------------------------------
var map = new ol.Map({
  target: 'map',
  controls: ol.control.defaults().extend([
    new ol.control.ScaleLine()
  ]),
  layers: [
    new ol.layer.Group({
      title: 'Mapas base',
      layers: [
          new ol.layer.Tile({
              title: 'Stamen toner',
              type: 'base',
              visible: false,
              source: new ol.source.Stamen({layer: 'toner'})
          }),
          new ol.layer.Tile({
              title: 'OpenStreetMap',
              type: 'base',
              visible: true,
              source: new ol.source.OSM()
          }),
          new ol.layer.Tile({
              title: 'Satellite',
              type: 'base',
              visible: false,
              source: new ol.source.MapQuest({layer: 'sat'})
          })
      ]
    }),
    new ol.layer.Group({
      title: 'Casters',
      layers: [
        vectorPointsBASE,
        vectorPointsIGS,
        vectorPointsIBGE,
        vectorPointsIGN,
        vectorPointsREGNA_ROU,
        vectorPointsUNR
      ]
    })
  ],
  view: new ol.View({
    center: ol.proj.transform([-60.5, -27], 'EPSG:4326', 'EPSG:3857'),
    zoom: 3.3
  })
});

// LayerSwitch  ----------------------------------------------------------------
var layerSwitcher = new ol.control.LayerSwitcher({
    tipLabel: 'Capas' // Optional label for button
});
map.addControl(layerSwitcher);

// Popup -----------------------------------------------------------------------
var popup = new ol.Overlay.Popup();
map.addOverlay(popup);
map.on('click', function(evt) {
  var feature = map.forEachFeatureAtPixel(evt.pixel,
      function(feature, layer) {
        return feature;
      });
  if (feature) {
    var coord = feature.getGeometry().getCoordinates();
    var content = '<ul><li>'+feature.get('coordinates')+'<li>'+feature.get('identifier')+', '+feature.get('country')+'<li>'+feature.get('data_format')+', '+feature.get('nav_system')+'<li>'+feature.get('misc')+'</ul>';
    popup.setPosition(coord);
    popup.show(evt.coordinate, '<div style="font-size:small"><span style="font-weight:bold;font-size:20px;color:'+feature.get('color')+'">'+feature.get('name')+'</span>' + content + '</div>');
  }
});
