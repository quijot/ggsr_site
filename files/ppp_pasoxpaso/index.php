<?php require 'upload.php'; ?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns# article: http://ogp.me/ns/article# " lang="es">

<head>
  <meta charset="utf-8">
  <meta name="description" content="PPP">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Servicio PPP | GGSR</title>
  <link href="../assets/css/all-nocdn.css" rel="stylesheet" type="text/css">
  <link rel="alternate" type="application/rss+xml" title="RSS" href="../rss.xml">
  <link rel="canonical" href="https://www.fceia.unr.edu.ar/gps/ppp/">
  <link rel="icon" href="../favicon.ico" sizes="128x128">
  <meta name="author" content="GGSR">
  <!-- reCAPTCHA3 -->
  <script src="https://www.google.com/recaptcha/api.js?render=6Le6TeUUAAAAAGTKVT-gwzRwHdy6xc8u2bnzeUcE"></script>
  <script>
    grecaptcha.ready(function() {
      grecaptcha.execute('6Le6TeUUAAAAAGTKVT-gwzRwHdy6xc8u2bnzeUcE', {
        action: 'upload_rinex_form'
      }).then(function(token) {
        var recaptchaResponse = document.getElementById('recaptcha_response');
        recaptchaResponse.value = token;
      });
    });
  </script>
  <!-- brython -->
  <script type="text/javascript" src="js/brython.js"></script>
  <script type="text/javascript" src="js/brython_stdlib.js"></script>
</head>

<body onload="brython({debug: 1, indexedDB: false})">
  <a href="#content" class="sr-only sr-only-focusable">Ir al contenido principal</a>
  <!-- Menubar -->
  <nav class="navbar navbar-default navbar-static-top">
    <div class="container">
      <!-- This keeps the margins nice -->
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-navbar" aria-controls="bs-navbar" aria-expanded="false">
          <span class="sr-only">Mostrar navegación</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="https://www.fceia.unr.edu.ar/gps/">
          <span id="blog-title">GGSR</span>
        </a>
      </div>
      <!-- /.navbar-header -->
      <div class="collapse navbar-collapse" id="bs-navbar" aria-expanded="false">
        <ul class="nav navbar-nav">
          <li>
            <a href="../investigacion/">Investigación</a>
          </li>
          <li>
            <a href="../cursos/">Cursos</a>
          </li>
          <li>
            <a href="../extension/">Extensión</a>
          </li>
          <li>
            <a href="../publicaciones/">Publicaciones</a>
          </li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Software <b class="caret"></b></a>
            <ul class="dropdown-menu">
              <li>
                <a href="../ppp/">Servicio PPP</a>
              </li>
              <li>
                <a href="../calc/">Calculadora ITRF → POSGAR</a>
              </li>
              <li>
                <a href="../software/">Otros</a>
              </li>
            </ul>
          </li>
          <li>
            <a href="../historia/">Historia</a>
          </li>
          <li>
            <a href="../contacto/">Contacto</a>
          </li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
          <li>
            <a href="index.txt" id="sourcelink">Código fuente</a>
          </li>
        </ul>
      </div>
      <!-- /.navbar-collapse -->
    </div>
    <!-- /.container -->
  </nav><!-- End of Menubar -->
  <div class="container" id="content" role="main">
    <div class="body-content">
      <!--Body content-->
      <div class="row">
        <article class="post-text storypage" itemscope="itemscope" itemtype="http://schema.org/Article">
          <header>
            <h1 class="p-name entry-title" itemprop="headline name"><a href="." class="u-url">Servicio PPP</a></h1>
          </header>
          <div class="e-content entry-content" itemprop="articleBody text">
            <div class="well col-lg-12">
              <form class="form-horizontal col-lg-6" id="upload-form" method="post" action="upload.php?upload=true" enctype="multipart/form-data">
                <fieldset id="upload-fieldset">
                  <legend>1. Carga del RINEX</legend>
                  <div class="form-group">
                    <!-- MAX_FILE_SIZE must precede the file input field -->
                    <input type="hidden" name="MAX_FILE_SIZE" value="5242880">
                    <!-- RINEX -->
                    <label for="rinex" class="col-lg-2 control-label">Archivo RINEX</label>
                    <div class="col-lg-10">
                      <input type="file" class="form-control" id="rinex" name="file" accept=".gz,.zip,.Z">
                      <span class="help-block">
                        Comprimido (<code>gz</code>, <code>zip</code>, <code>Z</code>). Tamaño máximo
                        <code>5 MB</code>.
                        Para comprimir: <a href="https://terras.gsi.go.jp/ja/crx2rnx.html">Hatanaka</a>,
                        <a href="https://www.7-zip.org/">7zip</a>.
                      </span>
                    </div>
                  </div>
                  <div class="form-group">
                    <!-- MESSAGE -->
                    <div class="col-lg-8 col-lg-offset-2">
                      <div id="wait-msg" style="display: none">
                        <p>Cargando el archivo RINEX. El proceso puede demorar varios minutos, <strong class="text-warning">por
                            favor no cierre el navegador</strong>.</p>
                        <p>Una vez finalizado, se podrá enviar al servicio CSRS-PPP de la
                          <strong>N</strong>atural <strong>R</strong>esources
                          <strong>Can</strong>ada.</p>
                      </div>
                    </div>
                    <!-- SUBMIT -->
                    <div class="col-lg-2 pull-right">
                      <button type="submit" class="btn btn-primary pull-right">Cargar</button>
                    </div>
                  </div>
                </fieldset>
                <input type="hidden" name="recaptcha_response" id="recaptcha_response">
                <input type="hidden" name="action" value="upload_rinex_form">
              </form>
              <form class="form-horizontal col-lg-3" id="ppp-form" method="post" action="ppp.php">
                <fieldset id="ppp-fieldset">
                  <legend>2. Envío a CSRS-PPP</legend>
                  <div class="form-group">
                    <label for="rinex-list" class="col-lg-6 col-offset-6 control-label">Archivos RINEX</label>
                    <div id="rinex-list-target" class="col-lg-12">
                      <?php echo load_files_to_select('rinex-list', 'upload/', '*.{gz,zip,Z}'); ?>
                    </div>
                    <div id="loading" style="display:none"><img src="loading.svg"></div>
                    <span class="col-lg-12 help-block">Se conservan por 24hs.</span>
                  </div>
                  <div class="form-group">
                    <div class="btn-group pull-right">
                      <button id="ppp-process" type="submit" class="btn btn-warning pull-right">Procesar PPP</button>
                    </div>
                  </div>
                </fieldset>
              </form>
              <form class="form-horizontal col-lg-3" id="i2p-form" action="javascript:void(0);">
                <fieldset id="i2p-fieldset">
                  <legend>3. Transformar a POSGAR</legend>
                  <div class="form-group">
                    <label for="sum-list" class="col-lg-6 col-offset-6 control-label">Resultados PPP</label>
                    <div id="sum-list-target" class="col-lg-12">
                      <?php echo load_files_to_select('sum-list', 'ppp_results/', '*.sum'); ?>
                    </div>
                    <div id="sending" style="display:none"><img src="sending.svg">
                      <p>Enviando a CSRS-PPP. El procesamiento puede demorar hasta 5 minutos. <strong>Por favor, espere.</strong></p>
                    </div>
                    <span class="col-lg-12 help-block">Se conservan por un mes.</span>
                  </div>
                  <div class="form-group">
                    <div class="btn-group pull-right">
                      <button type="submit" class="btn btn-success pull-right">Transformar</button>
                    </div>
                  </div>
                </fieldset>
              </form>
              <div class="col-lg-6">
                <p>
                  Este paso consiste en cargar el archivo RINEX que desea ser procesado
                  por el servicio PPP. Una vez cargado, el mismo aparecerá seleccionado
                  en la lista de <strong>Archivos RINEX</strong> disponibles y permanecerá
                  allí durante 24 hs.
                </p>
                <p>
                  <strong>Importante</strong>: comprimir RINEX con, al menos, algún
                  compresor <code>gz</code> o <code>zip</code> como por ejemplo
                  <a href="https://www.7-zip.org/">7zip</a>. De lo contrario NO SE
                  ACEPTARÁ. Es óptimo comprimir además con
                  <a href="https://terras.gsi.go.jp/ja/crx2rnx.html">Hatanaka</a>.
                </p>
              </div>
              <div class="col-lg-3">
                <p>
                  Seleccionar un RINEX para enviar a proceso por PPP. Luego de unos
                  minutos el resultado aparecerá en <strong>Resultados PPP</strong>.
                </p>
              </div>
              <div class="col-lg-3">
                <p>
                  Seleccionar un RINEX para enviar a proceso por PPP. Luego de unos
                  minutos el resultado aparecerá en <strong>Resultados PPP</strong>.
                </p>
              </div>
            </div>
            <script src="../assets/js/all-nocdn.js"></script>
            <script src="../assets/js/colorbox-i18n/jquery.colorbox-es.js"></script>
            <!-- <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script> -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.min.js" integrity="sha384-FzT3vTVGXqf7wRfy8k4BiyzvbNfeYjK+frTVqZeNDFl8woCbF0CYG6g2fMEFFo/i" crossorigin="anonymous"></script>
            <script>
              $(document).ready(function() {
                $('#upload-form').submit(function(event) {
                  /*** Carga de archivo RINEX ***/
                  if ($('#rinex').val()) {
                    event.preventDefault();
                    $('#wait-msg').show();
                    $('#rinex-list').hide();
                    $('#loading').show();
                    $('#upload-fieldset').attr('disabled', true);
                    $(this).ajaxSubmit({
                      target: '#rinex-list-target',
                      beforeSubmit: function() {
                        // $('#progress').text('0%');
                        // FALTA PROGRESSBAR
                      },
                      uploadProgress: function(event, position, total, percentageComplete) {
                        // $('#progress').text(percentageComplete + '%');
                        // FALTA PROGRESSBAR
                      },
                      success: function() {
                        $('#loading').hide();
                        $('#rinex-list').show();
                        $('#wait-msg').hide();
                        // $('#progress').text('100%');
                      },
                      resetForm: true
                    });
                  }
                  return false;
                });
                $('#ppp-form').submit(function(event) {
                  /*** Envío de RINEX a CSRS-PPP ***/
                  if ($('#rinex-list').val()) {
                    if (confirm("¡ATENCIÓN! Va a enviar el RINEX seleccionado al Servicio CSRS-PPP de la NRCan. Esto puede demorar hasta 5 minutos. ¿Desea continuar?")) {
                      event.preventDefault();
                      $('#upload-fieldset').attr('disabled', true);
                      $('#rinex-list').hide();
                      $('#sending').show();
                      $(this).ajaxSubmit({
                        target: '#rinex-list-target',
                        beforeSubmit: function() {
                          // $('#progress').text('0%');
                          // FALTA PROGRESSBAR
                        },
                        uploadProgress: function(event, position, total, percentageComplete) {
                          // $('#progress').text(percentageComplete + '%');
                          // FALTA PROGRESSBAR
                        },
                        success: function() {
                          $('#sending').hide();
                          $('#rinex-list').show();
                          // $('#progress').text('100%');
                        },
                        resetForm: true
                      });
                    }
                  }
                  return false;
                });
              });
            </script>
          </div>
      </div>
      </article>
    </div>
    <!--End of body content-->
    <footer id="footer">
      Universidad Nacional de Rosario || Facultad de Ciencias Exactas, Ingeniería y Agrimensura<br>Contenidos © 2020
      <a href="mailto:ggsr@fceia.unr.edu.ar">GGSR</a> - Empoderado por <a href="http://getnikola.com" rel="nofollow">Nikola</a>
      <a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/deed.es_AR"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-sa/4.0/80x15.png"></a>
    </footer>
  </div>
  </div>
  <!-- Google Analytics -->
  <script>
    (function(i, s, o, g, r, a, m) {
      i['GoogleAnalyticsObject'] = r;
      i[r] = i[r] || function() {
        (i[r].q = i[r].q || []).push(arguments)
      }, i[r].l = 1 * new Date();
      a = s.createElement(o),
        m = s.getElementsByTagName(o)[0];
      a.async = 1;
      a.src = g;
      m.parentNode.insertBefore(a, m)
    })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');
    ga('create', 'UA-22884999-2', 'auto');
    ga('send', 'pageview');
  </script>
</body>

</html>