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
Usuario de datos del Caster SIRGAS Experimental</h1>

<h4>Por favor, complete este formulario para solicitar libre acceso a los
streams de datos GNSS en tiempo real del Caster SIRGAS Experimental</h4>
 
<table class="centrada">
<tr><td>
<ul id="menu">
    <li><a href="." title="Vuelve a la p&aacute;gina principal del sitio">Volver al inicio</a></li>
</ul>
</td></tr>
</table>

<?php
// Parámetros de armado de archivo
$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$instit = $_POST['institucion'];
$direcc = $_POST['direccion'];
$ciudad = $_POST['ciudad'];
$telefo = $_POST['telefono'];
$descri = $_POST['descripcion'];

// Variables útiles
$caster_dir = 'reg/user/';
$sent = 'sent/';
$planilla_dir = 'planilla/';
$planilla_file = 'planilla';

if(isset($_POST['submit']) and isset($_POST['nombre']) and 
  isset($_POST['correo']) and !file_exists($caster_dir.$correo)) {
    $resp = recaptcha_check_answer ($privatekey,
                                    $_SERVER["REMOTE_ADDR"],
                                    $_POST["recaptcha_challenge_field"],
                                    $_POST["recaptcha_response_field"]);
    if ($resp->is_valid) {
        # escribe el archivo individual que se envía al mail
        echo exec('echo "Nombre:      '.$nombre.'" >  '.$caster_dir.$correo);
        echo exec('echo "Correo:      '.$correo.'" >> '.$caster_dir.$correo);
        echo exec('echo "Institucion: '.$instit.'" >> '.$caster_dir.$correo);
        echo exec('echo "Direccion:   '.$direcc.'" >> '.$caster_dir.$correo);
        echo exec('echo "Ciudad/Pais: '.$ciudad.'" >> '.$caster_dir.$correo);
        echo exec('echo "Telefono:    '.$telefo.'" >> '.$caster_dir.$correo);
        echo exec('echo "Descripcion: '.$descri.'" >> '.$caster_dir.$correo);
        # escribe el archivo planilla con todos los datos
        echo exec('echo "'.$nombre.';'.$correo.';'.$instit.';'.$direcc.';'.$ciudad.';'.$telefo.';'.$descri.'" >> '.$caster_dir.$sent.$planilla_dir.$planilla_file);
        
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
<form id="user" method="post" action="<?php echo $PHP_SELF ?>">
<table class="tabla_con_bordes justify" width="60%">
<tr>
<td>
    <p><label for="nombre">Nombre * </label><input id="nombre" name="nombre" type="text" class="input" maxlength="255" value=""></p>
    <p><label for="correo">e-mail * </label><input id="correo" name="correo" type="text" class="input" maxlength="255" value=""></p>
    <p><label for="institucion">Instituci&oacute;n </label><input id="institucion" name="institucion" type="text" class="input" maxlength="255" value=""></p>
    <p><label for="direccion">Direcci&oacute;n </label><input id="direccion" name="direccion" type="text" class="input" maxlength="255" value=""></p>
    <p><label for="ciudad">Ciudad/Pa&iacute;s </label><input id="ciudad" name="ciudad" type="text" class="input" maxlength="255" value=""></p>
    <p><label for="telefono">Tel&eacute;fono </label><input id="telefono" name="telefono" type="text" class="input" maxlength="255" value=""></p>
    <p><label for="descripcion">Breve descripci&oacute;n de su aplicaci&oacute;n de tiempo real </label><textarea id="descripcion" name="descripcion" class="input"></textarea></p>
    <div align="right"><?php echo recaptcha_get_html($publickey, $error); ?></div>
    <p>Los campos marcados con <strong>*</strong> son obligatorios.</p>
    <div><p><strong>El ID de usuario y la contrase&ntilde;a que 
     recibir&aacute; por correo electr&oacute;nico en respuesta a su 
     solicitud son v&aacute;lidos s&oacute;lo para uso personal, su 
     entrega a terceras personas no es posible. Le agradecemos mantener
     la confidencialidad.</strong></p></div>
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
    <p>Al enviar este formulario usted confirma la veracidad de sus 
     datos personales y acepta las condiciones del servicio antes de 
     registrarse.</p>
    <p>Condiciones del servicio: Al completar esta inscripci&oacute;n y / o uso
     del servicio, el suscriptor se compromete a aceptar el servicio 
     como es, y tambi&eacute;n reconoce que SIRGAS (Sistema de 
     Referencia Geoc&eacute;ntrico para Las Am&eacute;ricas) no otorga 
     ninguna garant&iacute;a, impl&iacute;cita o expl&iacute;cita de la 
     exactitud o la disponibilidad del Servicio. El Usuario 
     tambi&eacute;n acepta que el Servicio puede tener fallas y 
     degradaciones en la precisi&oacute;n y que estos eventos pueden 
     hacer que el servicio resulte no apto para cualquier uso. Si bien 
     SIRGAS se preocupa por ofrecer el servicio en las mejores 
     condiciones posibles, pueden presentarse errores, omisiones o 
     inconsistencias involuntarias, por lo cual SIRGAS no puede asumir 
     ninguna responsabilidad por da&ntilde;os o perjuicios materiales o 
     no materiales causados por el uso de este servicio. En consecuencia, 
     el mismo usuario es responsable de indemnizar, defender y mantener 
     SIRGAS y sus afiliados de cualquier p&eacute;rdida o da&ntilde;o resultante 
     de cualquier reclamo por cualquier persona en relaci&oacute;n con 
     los servicios de datos previstos en el presente acuerdo.</p>
    <p>Finalmente, tenga en cuenta que los datos est&aacute;n disponibles 
     principalmente para fines de demostraci&oacute;n y evaluaci&oacute;n. 
     SIRGAS tiene como objetivo proporcionar un servicio ininterrumpido. 
     A pesar de todos los esfuerzos pueden ocurrir interrupciones. Es 
     importante comprender que los streams pueden ser interrumpidos o no 
     estar disponibles en cualquier momento sin previo aviso.</p>
    </div>
</td>
</tr>
</table>

<?php
footer();
?>

</body>

</html>
