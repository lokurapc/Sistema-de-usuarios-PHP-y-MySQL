<?php
require_once('config.php');

$resultado = mysqli_query($conexion,"SELECT DATABASE()");

if ($resultado) {
	$query = "CREATE TABLE IF NOT EXISTS users(
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY(id),
	username VARCHAR(30) UNIQUE NOT NULL,
	password VARCHAR(255) NOT NULL,
	email VARCHAR(60) UNIQUE NOT NULL,
	fechadealta VARCHAR(12) NOT NULL,
	confirmacion VARCHAR(64) NOT NULL,
	ultimoingreso VARCHAR(12) DEFAULT NULL,
	intentos VARCHAR(3) DEFAULT NULL,
	autologinhash VARCHAR(64) DEFAULT NULL,
	fechaautologin VARCHAR(12) NULL,
	resetpassword VARCHAR(64) DEFAULT NULL,
	fechareset VARCHAR(12) NULL)";

	mysqli_query($conexion,$query) or die(mysqli_error($conexion));
}
mysqli_close($conexion);
?>
<!DOCTYPE html>
<html>
<head>
<title>Instalación</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
<div class="register">
<?php
if (!$resultado) {
?>
	<form action="<?=$_SERVER["SCRIPT_NAME"];?>" method="POST">
		<h2><b>Instalar el sistema de Usuarios y MySQL</b></h2>
		<p><button type="submit" class="btn blue"><i class="fa fa-wrench"></i><b> Instalar el sistema</b></button></p>
	</form>
<?php
} else {
?>
<h2><b>La tabla ya estaba creada</b></h2>
<?php
$row = mysqli_fetch_row($resultado);
echo "<p>La base de datos seleccionada es: ".$row[0]."</p>\n";
mysqli_free_result($resultado);
?>
<p><b><a href="index.php">Ir al inicio de sesión</a></b></p>
<?php } ?>
</div>
</body>
</html>