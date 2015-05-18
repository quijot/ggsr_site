.. title: Formulario de Registro
.. slug: caster-user
.. date: 24/05/14 13:54:23 UTC-03:00
.. tags: 
.. link: 
.. description: Formulario de registro usuario Caster SIRGAS experimental
.. type: text

<!-- reCAPTCHA -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<h2>Usuario de datos del Caster SIRGAS Experimental</h2>

<p>Por favor, complete este formulario para solicitar libre acceso a los
streams de datos GNSS en tiempo real del Caster SIRGAS Experimental</p>

<div class="well">
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
  $caster_dir = '../reg/user/'; # relativo a ubicación de este archivo
  $sent = 'sent/';              # relativo a $caster_dir
  $planilla_dir = 'planilla/';  # relativo a $sent
  $planilla_file = 'planilla';  # relativo a $planilla_dir

  if(isset($_POST['submit'])) {
    if(!empty($nombre) and !empty($correo) and
       !file_exists($caster_dir.$correo) and $_POST['g-recaptcha-response']) {
      # escribe el archivo individual que se enví­a al mail
      echo exec('echo "Nombre:      '.$nombre.'" >  '.$caster_dir.$correo);
      echo exec('echo "Correo:      '.$correo.'" >> '.$caster_dir.$correo);
      echo exec('echo "Institucion: '.$instit.'" >> '.$caster_dir.$correo);
      echo exec('echo "Direccion:   '.$direcc.'" >> '.$caster_dir.$correo);
      echo exec('echo "Ciudad/Pais: '.$ciudad.'" >> '.$caster_dir.$correo);
      echo exec('echo "Telefono:    '.$telefo.'" >> '.$caster_dir.$correo);
      echo exec('echo "Descripcion: '.$descri.'" >> '.$caster_dir.$correo);
      # escribe el archivo planilla con todos los datos
      echo exec('echo "'.$nombre.';'.$correo.';'.$instit.';'.$direcc.';'.
                $ciudad.';'.$telefo.';'.$descri.'" >> '.$caster_dir.$sent.
                $planilla_dir.$planilla_file);
      
      echo '<h2 class="text-success">Sus datos fueron registrados</h2>
            <h4>Si su correo es una dirección válida, en breve le llegarán
            los datos para ingresar.</h4>';
    }
    else { // algún ERROR
      echo '<h2 class="text-danger">Hubo algún error en los datos.</h2>
            <p class="text-danger">Por favor vuelva a intentarlo.</p>';
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
  <form class="form-horizontal" id="user" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
    <fieldset>
      <legend>Registro de usuarios Caster SIRGAS experimental</legend>
      <div class="form-group">
        <label for="nombre" class="col-lg-2 control-label">Nombre *</label>
        <div class="col-lg-10">
          <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre">
        </div>
      </div>
      <div class="form-group">
        <label for="correo" class="col-lg-2 control-label">e-mail *</label>
        <div class="col-lg-10">
          <input type="text" class="form-control" id="correo" name="correo" placeholder="Correo electrónico">
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
  <p>Condiciones del servicio: Al completar esta inscripción y/o uso del 
  servicio, el suscriptor se compromete a aceptar el servicio como es, y 
  también reconoce que SIRGAS (Sistema de Referencia Geocéntrico para Las 
  Américas) no otorga ninguna garantía, implícita o explícita de la exactitud 
  o la disponibilidad del Servicio. El Usuario también acepta que el Servicio 
  puede tener fallas y degradaciones en la precisión y que estos eventos pueden 
  hacer que el servicio resulte no apto para cualquier uso. Si bien SIRGAS se 
  preocupa por ofrecer el servicio en las mejores condiciones posibles, pueden 
  presentarse errores, omisiones o inconsistencias involuntarias, por lo cual 
  SIRGAS no puede asumir ninguna responsabilidad por daños o perjuicios 
  materiales o no materiales causados por el uso de este servicio. En 
  consecuencia, el mismo usuario es responsable de indemnizar, defender y 
  mantener SIRGAS y sus afiliados de cualquier pérdida o dano resultante de 
  cualquier reclamo por cualquier persona en relación con los servicios de 
  datos previstos en el presente acuerdo.</p>
  <p>Finalmente, tenga en cuenta que los datos están disponibles principalmente 
  para fines de demostración y evaluación. SIRGAS tiene como objetivo 
  proporcionar un servicio ininterrumpido. A pesar de todos los esfuerzos 
  pueden ocurrir interrupciones. Es importante comprender que los streams 
  pueden ser interrumpidos o no estar disponibles en cualquier momento sin 
  previo aviso.</p>
</div>
