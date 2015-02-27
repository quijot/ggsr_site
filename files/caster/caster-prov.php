<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">

<?php
require("../ep/several.php");

require_once('recaptchalib.php');
// Get a key from https://www.google.com/recaptcha/admin/create
$publickey = "6LfTL_sSAAAAAPCPWjJpHou92vL88FS6DLjB9uJA";
$privatekey = "6LfTL_sSAAAAAMs4O738rK0e4MZ_MC-lPtt9z5HT";
?>

<html>

<head>
    <title>Formulario de registro</title>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
    <!-- CSS del sitio -->
    <link type="text/css" rel="stylesheet" href="../ep/style.css">
</head>

<body>

<h1>Formulario de Registro<br>
Proveedor de datos al Caster SIRGAS Experimental</h1>

<div style="width: 60%; margin: auto;">
<h4>Muchas gracias por su intenci&oacute;n de publicar datos en tiempo 
real a trav&eacute;s de Caster SIRGAS Experimental, por favor complete 
el formulario para solicitar un ID y contrase&ntilde;a de acceso al 
servidor para cargar el stream de datos GNSS en tiempo real de una 
estaci&oacute;n permanente integrada a SIRGAS-CON utilizando el 
protocolo NTRIP.</h4>
</div>
 
<table class="centrada">
<tr><td>
<ul id="menu">
    <li><a href="." title="Vuelve a la p&aacute;gina principal del sitio">Volver al inicio</a></li>
</ul>
</td></tr>
</table>

<?php
// Parámetros de armado de archivo
$id_ep  = $_POST['id_ep'];
$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$instit = $_POST['institucion'];
$direcc = $_POST['direccion'];
$ciudad = $_POST['ciudad'];
$telefo = $_POST['telefono'];
$descri = $_POST['descripcion'];

// Variables útiles
$caster_dir = 'reg/prov/';

if(isset($_POST['submit']) and $nombre!='' and $correo!='' and 
  $id_ep!='' and !file_exists($caster_dir.$id_ep)) {
    $resp = recaptcha_check_answer ($privatekey,
                                    $_SERVER["REMOTE_ADDR"],
                                    $_POST["recaptcha_challenge_field"],
                                    $_POST["recaptcha_response_field"]);
    if ($resp->is_valid) {
        echo exec('echo "ID EP:       '.$id_ep.'"  >  '.$caster_dir.$id_ep);
        echo exec('echo "Nombre:      '.$nombre.'" >> '.$caster_dir.$id_ep);
        echo exec('echo "Correo:      '.$correo.'" >> '.$caster_dir.$id_ep);
        echo exec('echo "Institucion: '.$instit.'" >> '.$caster_dir.$id_ep);
        echo exec('echo "Direccion:   '.$direcc.'" >> '.$caster_dir.$id_ep);
        echo exec('echo "Ciudad/Pais: '.$ciudad.'" >> '.$caster_dir.$id_ep);
        echo exec('echo "Telefono:    '.$telefo.'" >> '.$caster_dir.$id_ep);
        echo exec('echo "Descripcion: '.$descri.'" >> '.$caster_dir.$id_ep);
        
        echo "<h1>Sus datos fueron registrados</h1><h4>Si su correo es 
         una direcci&oacute;n v&aacute;lida, en breve le llegar&aacute;n los
         datos para ingresar.</h4>";
    } else {
        # set the error code so that we can display it
        $error = $resp->error;
        echo '<h2>Hubo alg&uacute;n error. <a href=".">Vuelva</a> y complete correctamente el formulario.</h2>';
    }
}
else { // FORMULARIO
?>
<form id="prov" method="post" action="<?php echo $PHP_SELF ?>">
<table class="tabla_con_bordes justify" width="60%">
<tr>
<td>
    <p><label for="id_ep">ID de la estaci&oacute;n permanente * </label><input id="id_ep" name="id_ep" type="text" class="input" maxlength="4" value=""></p>
    <p><label for="institucion">Instituci&oacute;n </label><input id="institucion" name="institucion" type="text" class="input" maxlength="255" value=""></p>
    <p><label for="direccion">Direcci&oacute;n </label><input id="direccion" name="direccion" type="text" class="input" maxlength="255" value=""></p>
    <p><label for="ciudad">Ciudad/Pa&iacute;s </label><input id="ciudad" name="ciudad" type="text" class="input" maxlength="255" value=""></p>
    <p><label for="telefono">Tel&eacute;fono </label><input id="telefono" name="telefono" type="text" class="input" maxlength="255" value=""></p>
    <p><label for="nombre">Nombre de contacto * </label><input id="nombre" name="nombre" type="text" class="input" maxlength="255" value=""></p>
    <p><label for="correo">e-mail de contacto * </label><input id="correo" name="correo" type="text" class="input" maxlength="255" value=""></p>
    <p><label for="descipcion">Breve descripci&oacute;n de su aplicaci&oacute;n de tiempo real </label><textarea id="descipcion" name="descipcion" class="input"></textarea></p>
    <div align="right"><?php echo recaptcha_get_html($publickey, $error); ?></div>
    <p>Los campos marcados con <strong>*</strong> son obligatorios.</p>
    <div><p><strong>El ID de usuario y la contrase&ntilde;a que 
     recibir&aacute; por correo electr&oacute;nico en respuesta a su 
     solicitud son v&aacute;lidos s&oacute;lo para uso de &eacute;sta 
     estaci&oacute;n permanente. Le agradecemos mantener la 
     confidencialidad el caso.</strong></p></div>
    <p><input id="sendForm" type="submit" class="button" name="submit" value="Enviar"></p>
</td>
</tr>
</table>
</form>
<?
}
?>

<table class="tabla_con_bordes justify" width="60%">
<tr>
<td>
    <div>
    <p>Al enviar este formulario usted confirma sus datos personales, la identificaci&oacute;n de la estaci&oacute;n permanente y acepta las condiciones del servicio antes de registrarse.</p>
    <p>Condiciones del servicio: En el marco del proyecto <em>SIRGAS en Tiempo Real</em> se ha puesto en funcionamiento un servicio denominado &quot;Caster SIRGAS Experimental&quot;, cuya finalidad es la publicaci&oacute;n de datos GNSS en tiempo real de las estaciones continuas integradas a la red SIRGAS-CON utilizando el protocolo NTRIP.</p>
    <p>El caster se encuentra alojado en el laboratorio del <a href="http://www.fceia.unr.edu.ar/gps"
     title="Grupo de Geodesia Satelital de Rosario">Grupo de Geodesia Satelital de Rosario (GGSR)</a>, de la 
     Universidad Nacional de Rosario, Argentina.</p>
    <p>La identificaci&oacute;n del caster es:</p>
    <p><strong>IP: 200.3.123.65</strong></p>
    <p><strong>Port: 2101</strong></p>
    <p>Como su nombre lo indica, este es un servicio experimental, hasta que se declare definitivo.</p>
    <p>Se dar&aacute; prioridad a aquellas estaciones permanentes que puedan garantizar una contribuci&oacute;n de datos a tiempo real a largo plazo. Si bien en esta primera etapa se aceptar&aacute;n streams &quot;a prueba&quot;.</p>
    <p>Los streams de datos GNSS que se carguen en el Caster SIRGAS Experimental ser&aacute;n publicados de manera libre y gratuita. Los interesados en tener acceso a los datos solo deber&aacute;n completar un formulario de registro.</p>
    <p>Los streams de datos deben contener los observables completos (c&oacute;digo y fase), se aceptan datos de receptores que observan solo GPS aunque es preferible GPS+GLONASS.</p>
    <p>Los streams deber&aacute;n ser transmitidos utilizando el protocolo NTRIP (ver 1.0 o 2.0), manteniendo el formato est&aacute;ndar RTCM (ver. 2. o 3.)</p>
    <p>Deber&aacute; proveerse un archivo sitelog de la estaci&oacute;n, vigente y actualizado a la fecha de registraci&oacute;n.</p>
    </div>
</td>
</tr>
</table>

<?php
footer();
?>

</body>

</html>
