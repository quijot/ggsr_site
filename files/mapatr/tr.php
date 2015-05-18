<?php echo exec('python src/py/query.py') ?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <title>Mapa TR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ol3/3.5.0/ol.css">
    <link rel="stylesheet" href="src/css/ol3-layerswitcher.css">
    <link rel="stylesheet" href="src/css/ol3-popup.css">
    <link rel="stylesheet" href="src/css/map.css">
  </head>
  <body>
    <div id="map" class="map"><div id="popup"></div></div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ol3/3.5.0/ol.js"></script>
    <script src="src/js/ol3-layerswitcher.js"></script>
    <script src="src/js/ol3-popup.js"></script>
    <script src="src/js/map.js"></script>
  </body>
</html>
