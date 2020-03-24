.. title: Servicio PPP del GGSR
.. slug: ppp
.. date: 24/03/2020 18:00:00 UTC-03:00
.. tags: ppp
.. link:
.. description: PPP
.. type: text

<script>
    function send() {
    document.getElementById("submit-btn").disabled = true;
    document.querySelector('#submit-btn').innerText = 'Procesando...';
    document.querySelector('#wait-msg').style.display = 'block';
    document.querySelector('#wait-msg').innerHTML = '<p>Enviando el archivo RINEX al servicio CSRS-PPP de la <strong>N</strong>atural <strong>R</strong>esources <strong>Can</strong>ada.</p><p>El proceso puede demorar varios minutos. Para poder ver los resultados, <strong>por favor no cierre el navegador</strong>.</p>';
    document.getElementById("captcha").style.display = "none";
    document.getElementById("ppp-form").action = "<?php echo htmlentities($_SERVER['PHP_SELF']); ?>";
    document.getElementById("ppp-form").submit(); // Submitting form
    }
</script>
<!-- reCAPTCHA -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<div class="well col-lg-12">
    <?php
    function printer($array)
    {
    foreach ($array as $key => $value) {
        echo $value . "\n";
    }
    };
    function execute($command)
    {
    exec($command, $output, $retval);
    if ($retval)
        echo "exec:<pre>" . $command . "</pre>";
    echo "output:<br><pre>";
    printer($output);
    echo "</pre>";
    if ($retval)
        echo "retval:<pre>" . $retval . "</pre>";
    return $retval;
    };
    if (strpos(htmlentities($_SERVER['HTTP_HOST']), "localhost") !== false)
    $captchaOk = true;
    else
    $captchaOk = $_POST['g-recaptcha-response'];
    if (!empty($_POST) && $captchaOk) {
    echo "<h2>Procesamiento</h2>";
    // Parameters
    $idw_n = $_POST["idw_n"];
    $idw_p = $_POST["idw_p"];
    if ($_POST["coordinates"]) {
        $c = explode(",", trim($_POST["coordinates"]));
        $lat = explode(" ", trim($c[0]));
        $lon = explode(" ", trim($c[1]));
        $latComp = trim($lat[0]) . ' ' . trim($lat[1]) . ' ' . trim($lat[2]);
        $lonComp = trim($lon[0]) . ' ' . trim($lon[1]) . ' ' . trim($lon[2]);
    }
    $tmpFile = $_FILES["rinex"]["tmp_name"];
    $rinexFileName = basename($_FILES["rinex"]["name"]);
    $targetDir = "uploads/";
    $targetFile = $targetDir . $rinexFileName;
    $rinexFileType = pathinfo($targetFile, PATHINFO_EXTENSION);
    $rinexFileBase = pathinfo($targetFile, PATHINFO_FILENAME);
    $rinexFileSize = round($_FILES["rinex"]["size"] / 1024, 3); // file size in KB
    $rinexMaxSize = 10 * 1024; // 10240 KB = 10 MB
    $pppResultDir = "results_ppp/";
    $pppSummary = $pppResultDir . pathinfo($rinexFileBase, PATHINFO_FILENAME) . ".sum";
    $cmdCSRS = "python3 csrs_ppp_auto.py --user_name santiagonob@gmail.com --ref ITRF --rnx " . $targetFile . " --results_dir ../" . $pppResultDir;
    $cmdPostPPP = 'python3 post_ppp.py ' . $pppSummary;
    if (!$idw_n || ($idw_n < 0 || $idw_n > 100))
        $cmdPostPPP = $cmdPostPPP . ' 3';
    else
        $cmdPostPPP = $cmdPostPPP . ' ' . $idw_n;
    if (!$idw_p || ($idw_n < 1 || $idw_n > 20))
        $cmdPostPPP = $cmdPostPPP . ' 1';
    else
        $cmdPostPPP = $cmdPostPPP . ' ' . $idw_p;
    if ($latComp && $lonComp)
        $cmdPostPPP = $cmdPostPPP . ' "' . $latComp . '" "' . $lonComp . '"';
    $uploadOk = true;
    $processedOk = false;
    // // Check CAPTCHA
    // if (!$captchaOk) {
    //   echo "<p>CAPTCHA no superado... ¿es Ud. humano?</p>";
    //   $uploadOk = false;
    // }
    // Check if file already exists
    if (file_exists($pppSummary)) {
        echo "<p>Ese archivo ya había sido procesado. Se utilizan resultados obtenidos previamente. Si desea volver a procesar, modifique el nombre del archivo RINEX.</p>";
        $uploadOk = true;
        $processedOk = true;
    }
    // Check if file already exists
    if (!$rinexFileName) {
        echo "<p>Debe adjuntar un archivo de observaciones.</p>";
        $uploadOk = false;
    }
    // Check file size
    if ($rinexFileSize > $rinexMaxSize) {
        echo "<p>El archivo es demasiado extenso.</p>";
        $uploadOk = false;
    }
    // Allow certain file formats
    if ($rinexFileType != "zip" && $rinexFileType != "gz" && $rinexFileType != "Z") {
        echo "<p>Solo se aceptan archivos .zip, .gz o .Z.</p>";
        $uploadOk = false;
    }
    // Process...
    if ($uploadOk) { // OK
        if ($processedOk)
        execute($cmdPostPPP);
        else {
        if (move_uploaded_file($tmpFile, $targetFile)) {
            echo "<p>El archivo " . $rinexFileName . " (" . $rinexFileSize . " KB) fue cargado correctamente.</p>";
            if (!execute($cmdCSRS))
            execute($cmdPostPPP);
        } else { // ERROR loading file
            echo '<h3 class="text-danger">Hubo algún problema durante la carga del archivo</h3>
                <p class="text-danger">Por favor vuelva a intentarlo.</p>';
        }
        }
    } else { // ERROR in form
        echo '<h3 class="text-danger">Hubo algún error en los datos.</h3>
            <p class="text-danger">Por favor vuelva a intentarlo.</p>';
    }
    } else { // FORM
    ?>
    <form class="form-horizontal" id="ppp-form" method="post" enctype="multipart/form-data">
        <fieldset>
        <legend>Servicio NRCan <strong>CSRS-PPP</strong> + Transfrmación GGSR <strong>ITRF→POSGAR</strong></legend>
        <div class="form-group">
            <!-- RINEX -->
            <label for="rinex" class="col-lg-2 control-label">Archivo RINEX</label>
            <div class="col-lg-10">
            <input type="file" class="form-control" id="rinex" name="rinex">
            </div>
        </div>
        <legend>Configuración opcional</legend>
        <div class="form-group">
            <!-- Coordinates (optional) -->
            <label for="coordinates" class="col-lg-2 control-label">Conocidas</label>
            <div class="col-lg-10">
            <input type="text" class="form-control" id="coordinates" name="coordinates" placeholder="-31 14 51.30585, -61 28 16.28025">
            <span class="help-block">Coordenadas POSGAR07 para comparar con los resultados.</span>
            </div>
        </div>
        <!-- IDW parameters (default n=3, p=1) -->
        <div class="form-group">
            <label for="idw_n" class="col-lg-2 control-label">Parámetros IDW (n)</label>
            <div class="col-lg-4">
            <input type="number" class="form-control" id="idw_n" name="idw_n" placeholder="3" min=1>
            <span class="help-block">Cantidad de EP para interpolar. Puede ser [1-100]. (Por defecto es 3)</span>
            </div>
            <label for="idw_p" class="col-lg-2 control-label">IDW (p)</label>
            <div class="col-lg-4">
            <input type="number" class="form-control" id="idw_p" name="idw_p" placeholder="1" min=1 max=20>
            <span class="help-block">Ponderación de la distancia. A mayor valor, mayor influencia de EP más cercanas. Puede ser [1-20]. (Por defecto es 1)</span>
            </div>
        </div>
        <div class="form-group">
            <!-- CAPTCHA -->
            <div class="col-lg-10 col-lg-offset-2">
            <div id="captcha" class="g-recaptcha" data-sitekey="6LfTL_sSAAAAAPCPWjJpHou92vL88FS6DLjB9uJA"></div>
            </div>
        </div>
        <div class="form-group">
            <!-- MESSAGE -->
            <div class="col-lg-10 col-lg-offset-2">
            <span id="wait-msg" style="display: none">
            </span>
            </div>
        </div>
        <div class="form-group">
            <!-- SUBMIT -->
            <div class="col-lg-12">
            <button type="submit" class="btn btn-primary pull-right" id="submit-btn" name="submit-btn" onclick="send()">Enviar</button>
            </div>
        </div>
        </fieldset>
    </form>
    <?php
    }
    ?>
</div>
