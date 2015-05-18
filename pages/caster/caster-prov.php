.. title: Formulario de Registro
.. slug: caster-prov
.. date: 24/05/14 13:54:23 UTC-03:00
.. tags: 
.. link: 
.. description: Formulario de registro proveedor Caster SIRGAS experimental
.. type: text

<!-- reCAPTCHA -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<h2>Proveedor de datos al Caster SIRGAS Experimental</h2>

<p>Muchas gracias por su intención de publicar datos en tiempo real a través de 
Caster SIRGAS Experimental, por favor complete el formulario para solicitar un 
ID y contraseña de acceso al servidor para cargar el stream de datos GNSS en 
tiempo real de una estación permanente integrada a SIRGAS-CON utilizando el 
protocolo NTRIP.</p>

<div class="well">
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
  $caster_dir = '../reg/prov/'; # relativo a ubicación de este archivo

  if(isset($_POST['submit'])) {
    if(!empty($nombre) and !empty($correo) and !empty($id_ep) and 
       !file_exists($caster_dir.$id_ep) and $_POST['g-recaptcha-response']) {
      # escribe el archivo individual que se enví­a al mail
      echo exec('echo "ID EP:       '.$id_ep.'"  >  '.$caster_dir.$id_ep);
      echo exec('echo "Nombre:      '.$nombre.'" >> '.$caster_dir.$id_ep);
      echo exec('echo "Correo:      '.$correo.'" >> '.$caster_dir.$id_ep);
      echo exec('echo "Institucion: '.$instit.'" >> '.$caster_dir.$id_ep);
      echo exec('echo "Direccion:   '.$direcc.'" >> '.$caster_dir.$id_ep);
      echo exec('echo "Ciudad/Pais: '.$ciudad.'" >> '.$caster_dir.$id_ep);
      echo exec('echo "Telefono:    '.$telefo.'" >> '.$caster_dir.$id_ep);
      echo exec('echo "Descripcion: '.$descri.'" >> '.$caster_dir.$id_ep);
      
      echo '<h2 class="text-success">Sus datos fueron registrados</h2>
            <h4>Si su correo es una dirección válida, en breve le llegarán
            los datos para ingresar.</h4>';
    }
    else { // algún ERROR
      echo '<h2 class="text-danger">Hubo algún error en los datos.</h2>
            <p class="text-danger">Por favor vuelva a intentarlo.</p>';
      if (empty($id_ep)) {
        echo '<p class="text-danger">Debe ingresar un identificador de EP.</p>';
      }
      if (empty($nombre)) {
        echo '<p class="text-danger">Debe ingresar un nombre.</p>';
      }
      if (empty($correo)) {
        echo '<p class="text-danger">Debe ingresar un correo electrónico.</p>';
      }
    }
  }
  else { // FORMULARIO
  ?>
  <form class="form-horizontal" id="prov" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
    <fieldset>
      <legend>Registro de proveedores de datos Caster SIRGAS experimental</legend>
      <div class="form-group">
        <label for="id_ep" class="col-lg-2 control-label">ID de la estación permanente * </label>
        <div class="col-lg-10">
          <input type="text" class="form-control" id="id_ep" name="id_ep" maxlength="4" style="text-transform:uppercase;">
        </div>
      </div>
      <div class="form-group">
        <label for="institucion" class="col-lg-2 control-label">Institución</label>
        <div class="col-lg-10">
          <input type="text" class="form-control" id="institucion" name="institucion" placeholder="Institución">
        </div>
      </div>
      <div class="form-group">
        <label for="direccion" class="col-lg-2 control-label">Dirección</label>
        <div class="col-lg-10">
          <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Dirección">
        </div>
      </div>
      <div class="form-group">
        <label for="ciudad" class="col-lg-2 control-label">Ciudad/País</label>
        <div class="col-lg-10">
          <input type="text" class="form-control" id="ciudad" name="ciudad" placeholder="Ciudad o País donde reside">
        </div>
      </div>
      <div class="form-group">
        <label for="telefono" class="col-lg-2 control-label">Teléfono</label>
        <div class="col-lg-10">
          <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Teléfono de contacto">
        </div>
      </div>
      <div class="form-group">
        <label for="nombre" class="col-lg-2 control-label">Nombre de contacto * </label>
        <div class="col-lg-10">
          <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre">
        </div>
      </div>
      <div class="form-group">
        <label for="correo" class="col-lg-2 control-label">e-mail de contacto *</label>
        <div class="col-lg-10">
          <input type="text" class="form-control" id="correo" name="correo" placeholder="Correo electrónico">
        </div>
      </div>
      <div class="form-group">
        <label for="descripcion" class="col-lg-2 control-label">Descripción</label>
        <div class="col-lg-10">
          <textarea class="form-control" rows="3" id="descripcion" name="descripcion"></textarea>
          <span class="help-block">Breve descripción de su aplicación de tiempo real.</span>
        </div>
      </div>
       <div class="form-group">
        <div class="col-lg-10 col-lg-offset-2">
          <div class="g-recaptcha" data-sitekey="6LfTL_sSAAAAAPCPWjJpHou92vL88FS6DLjB9uJA"></div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-lg-12">
          <button type="submit" class="btn btn-primary pull-right" id="sendForm" name="submit">Enviar</button>
        </div>
      </div>
      <div class="form-group">
        <div class="col-lg-10 col-lg-offset-2">
          <p>Los campos marcados con <strong>*</strong> son obligatorios.</p>
          <p><strong>El ID de usuario y la contraseña que recibirá por correo
          electrónico en respuesta a su solicitud son válidos sólo para uso
          personal, su entrega a terceras personas no es posible. Le agradecemos 
          mantener la confidencialidad.</strong></p>
        </div>
      </div>
    </fieldset>
  </form>
  <?php
  }
  ?>
</div>

<div class="well">
  <p>Al enviar este formulario usted confirma la veracidad de sus datos 
  personales y acepta las condiciones del servicio antes de registrarse.</p>
  <p>Condiciones del servicio: En el marco del proyecto <em>SIRGAS en Tiempo 
  Real</em> se ha puesto en funcionamiento un servicio denominado "Caster 
  SIRGAS Experimental", cuya finalidad es la publicación de datos GNSS en 
  tiempo real de las estaciones continuas integradas a la red SIRGAS-CON 
  utilizando el protocolo NTRIP.</p>
  <p>El caster se encuentra alojado en el laboratorio del 
  <a href="http://www.fceia.unr.edu.ar/gps"
     title="Grupo de Geodesia Satelital de Rosario">Grupo de Geodesia Satelital 
     de Rosario (GGSR)</a>, de la Universidad Nacional de Rosario, Argentina.
  </p>
  <p>La identificación del caster es:</p>
  <p><strong>IP: 200.3.123.65</strong></p>
  <p><strong>Port: 2101</strong></p>
  <p>Como su nombre lo indica, este es un servicio experimental, hasta que se 
  declare definitivo.</p>
  <p>Se dará prioridad a aquellas estaciones permanentes que puedan garantizar 
  una contribución de datos a tiempo real a largo plazo. Si bien en esta 
  primera etapa se aceptarán streams "a prueba".</p>
  <p>Los streams de datos GNSS que se carguen en el Caster SIRGAS Experimental 
  serán publicados de manera libre y gratuita. Los interesados en tener acceso 
  a los datos solo deberán completar un formulario de registro.</p>
  <p>Los streams de datos deben contener los observables completos (código y 
  fase), se aceptan datos de receptores que observan solo GPS aunque es 
  preferible GPS+GLONASS.</p>
  <p>Los streams deberán ser transmitidos utilizando el protocolo NTRIP (ver 
  1.0 o 2.0), manteniendo el formato estándar RTCM (ver. 2 o 3)</p>
  <p>Deberá proveerse un archivo sitelog de la estación, vigente y actualizado 
  a la fecha de registración.</p>
</div>
