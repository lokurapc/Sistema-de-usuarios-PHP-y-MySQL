<?php
//Previene que el usuario entre en este archivo directamente
if ($_SERVER['SCRIPT_FILENAME'] == __FILE__) {
	header("Location:index.php");
	exit();
}
if (substr($_SERVER['HTTP_HOST'], 0, 4) != "www.") {
	$url = "http://www.".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
	header("Location:$url");
	exit();
}

// Prevents javascript XSS attacks aimed to steal the session ID
ini_set("session.cookie_httponly", 1);
// Session ID cannot be passed through URLs
ini_set("session.use_only_cookies", 1);
// Uses a secure connection (HTTPS) if possible
//ini_set("session.cookie_secure", 1);

// Configura los datos de tu cuenta
$mysql_dbhost = "localhost";        // Puede ser "localhost" aunque también una URL o una IP
$mysql_dbname = "patric90_basededatos";  // Nombre de la base de datos
$mysql_dbuser = "patric90_eluser";  // Usuario de la base de datos
$mysql_dbpass = "uyLTYz4Sa3Mk";   // Contraseña de la base de datos

// Conectar a la base de datos
$conexion = mysqli_connect($mysql_dbhost,$mysql_dbuser,$mysql_dbpass) or die("No se pudo contectar.");
mysqli_select_db($conexion,$mysql_dbname) or die(mysqli_error($conexion));
?>