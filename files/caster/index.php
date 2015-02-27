<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">

<?php require("../ep/several.php"); ?>
 
<html> 
 
<head> 
	<title>Proyecto SIRGAS Tiempo Real</title> 
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1"> 
	<!-- CSS del sitio --> 
	<link type="text/css" rel="stylesheet" href="../ep/style.css">

	<!-- Google Analytics -->
	<script type="text/javascript">
		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', 'UA-22884999-2']);
		  _gaq.push(['_trackPageview']);

		  (function() {
		    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();
	</script>
</head> 
 
<body> 
 
<h1>Proyecto SIRGAS Tiempo Real</h1> 

<table class="centrada">
<tr><td>
<ul id="menu">
    <li><a href="/gps/" title="Vuelve a la p&aacute;gina principal del sitio">Volver al inicio de GGSR</a></li>
</ul>
</td></tr>
</table>
 
<table class="tabla_con_bordes justify"> 
    <tr> 
        <th><h2>Formularios de Registro al Caster SIRGAS Experimental</h2></th>
    </tr> 
    <tr>
        <td>
            <ul>
            <li><a href="caster-user.php" title="Ingrese aqu&iacute; para registrarse y tener acceso a los datos GNSS en tiempo real del Caster SIRGAS Experimental">Registro como Usuario</a> para acceso a los datos GNSS en tiempo real
            <li><a href="caster-prov.php" title="Ingrese aqu&iacute; para registrarse y obtener un ID como proveedor de datos GNSS para publicarlos en el Caster SIRGAS Experimental">Registro como Proveedor</a> para obtener un ID como proveedor de datos
            </ul>
        </td> 
    </tr> 
</table> 

<p class="link">Ante cualquier duda o sugerencia sobre el funcionamiento del caster escribir a <script type="text/javascript">
emailE=('noguera&#64;' + 'fceia&#46;unr&#46;edu&#46;ar');
emailN=('Gustavo Noguera');
document.write('<a href="mailto:' + emailE + '">' + emailN + '<\/a>');
</script><noscript> 
<em>Direcc&oacute;n de correo protegida por JavaScript.<br> 
Por favor, habilite JavaScript para contactarnos.</em> 
</noscript>.

<h5>Recomendamos los siguientes navegadores para obtener el &oacute;ptimo funcionamiento de este sitio:<br>
<!--(haciendo click sobre los logotipos se accede a la descarga)<br>-->
<a href="http://www.mozilla.org/firefox/new/"><img src="../ep/img/Firefox64x64.png" alt="Mozilla Firefox" width=50></a>
<a href="http://www.getchromium.org/"><img src="../ep/img/Chromium64x64.png" alt="Chromium Browser" width=50></a>
</h5>

<?php footer();?> 
</body> 
 
</html> 
