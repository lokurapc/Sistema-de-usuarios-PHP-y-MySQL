<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
<title>Pagina 1</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
<h3>Titulo</h3>
<p>Bienvenido, este texto lo puede leer cualquier persona.</p>
<?php
logedIn($conexion);
if (isset($_SESSION) && isset($_SESSION["s_username"])) {
	echo "<p>este texto lo puede leer solo el usuario logueado !</p>";
	echo "<p><a href='plogout.php'>Salir</a></p>";
}
?>
<p>Este texto también lo puede leer cualquier persona.</p>
</body>
</html>