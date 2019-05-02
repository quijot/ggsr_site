var fromProjection = new OpenLayers.Projection("EPSG:4326"); // transform from WGS 1984
var toProjection = new OpenLayers.Projection("EPSG:900913"); // to Spherical Mercator Projection
var extent = new OpenLayers.Bounds(-63.50, -41.00, -56.67, -28.00).transform(fromProjection,toProjection);
var options = {
  restrictedExtent : extent,
  //controls: []
};
// Mapa
var map     = new OpenLayers.Map("basicMap", options);
var osm     = new OpenLayers.Layer.OSM();
var markers = new OpenLayers.Layer.Markers( "Markers" );
var boxes   = new OpenLayers.Layer.Boxes( "Boxes" );
map.addLayer(osm);
map.addLayer(markers);
map.addLayer(boxes);
// UNRO marker
var unro = new OpenLayers.LonLat(-60.62842520833333, -32.95935293055556).transform(fromProjection, toProjection);
markers.addMarker(new OpenLayers.Marker(unro));
// Zona completa
boxes.addMarker(new OpenLayers.Marker.Box(extent, "black", 2));
// Fajas
var f1bounds = new OpenLayers.Bounds(-59.25, -41.00, -56.67, -28.00).transform(fromProjection,toProjection);
var f2bounds = new OpenLayers.Bounds(-61.50, -41.00, -59.25, -28.00).transform(fromProjection,toProjection);
var f3bounds = new OpenLayers.Bounds(-63.50, -41.00, -61.50, -28.00).transform(fromProjection,toProjection);
var f1box = new OpenLayers.Marker.Box(f1bounds, "black", 1);
var f2box = new OpenLayers.Marker.Box(f2bounds, "black", 1);
var f3box = new OpenLayers.Marker.Box(f3bounds, "black", 1);
f1box.events.register("click", f1box, function (e) {
  this.setBorder("red", 2);
  f2box.setBorder("black", 1);
  f3box.setBorder("black", 1);
  document.getElementById("mapa").innerHTML = "FAJA 1";
  document.getElementById("lonL").innerHTML = "-59º 15'";
  document.getElementById("lonR").innerHTML = "-56º 40'";
});
f2box.events.register("click", f2box, function (e) {
  this.setBorder("red", 2);
  f1box.setBorder("black", 1);
  f3box.setBorder("black", 1);
  document.getElementById("mapa").innerHTML = "FAJA 2";
  document.getElementById("lonL").innerHTML = "-61º 30'";
  document.getElementById("lonR").innerHTML = "-59º 15'";
});
f3box.events.register("click", f3box, function (e) {
  this.setBorder("red", 2);
  f1box.setBorder("black", 1);
  f2box.setBorder("black", 1);
  document.getElementById("mapa").innerHTML = "FAJA 3";
  document.getElementById("lonL").innerHTML = "-63º 30'";
  document.getElementById("lonR").innerHTML = "-61º 30'";
});
boxes.addMarker(f1box);
boxes.addMarker(f2box);
boxes.addMarker(f3box);
// Show Extents
map.zoomToMaxExtent();
// function cleanMap()
function cleanMap() { markers.clearMarkers(); };
// function mark()
function sign(x) { return x ? x < 0 ? -1 : 1 : 0; };
function mark() {
  cleanMap();
  londc = document.getElementById('londc').value;
  lonmc = document.getElementById('lonmc').value;
  lonsc = document.getElementById('lonsc').value;
  lon   = sign(londc)*(sign(londc)*londc + lonmc/60.0 + lonsc/3600.0);
  latdc = document.getElementById('latdc').value;
  latmc = document.getElementById('latmc').value;
  latsc = document.getElementById('latsc').value;
  lat   = sign(latdc)*(sign(latdc)*latdc + latmc/60.0 + latsc/3600.0);
  var m = new OpenLayers.LonLat(lon, lat).transform(fromProjection, toProjection);
  markers.addMarker(new OpenLayers.Marker(m));
};
