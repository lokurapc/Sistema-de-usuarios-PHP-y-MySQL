<?php
if (version_compare(phpversion(), '5.5.0', '<')) {
	exit("Necesita al menos PHP 5.5.0, su version actual de PHP es: ".PHP_VERSION);
}
require_once('config.php');
require_once('functions.php');
session_start();

$msgErr = $passErr = $npassErr = $cpassEmpty = "";
$oldpassword = $npassword = $cpassword = "";
$error = "";

if (isset($_SESSION) && isset($_SESSION["s_username"])) {
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		if (empty(test_input($_POST["oldpassword"]))) {
			$passErr = '<p class="error"><b>La contraseña es obligatoria</b></p>';
			$error = 1;
		} elseif (strlen(test_input($_POST["oldpassword"])) < 8) {
			$passErr = '<p class="error"><b>La contraseña debe tener por lo menos 8 caracteres</b></p>';
			$error = 1;
		} else {
			$oldpassword = test_input($_POST["oldpassword"]);
		}
		if (empty(test_input($_POST["npassword"]))) {
			$npassErr = '<p class="error"><b>La contraseña es obligatoria</b></p>';
			$error = 1;
		} elseif (strlen(test_input($_POST["npassword"])) < 8) {
			$npassErr = '<p class="error"><b>La contraseña debe tener por lo menos 8 caracteres</b></p>';
			$error = 1;
		} elseif (test_input($_POST["npassword"])!= test_input($_POST["cpassword"])) {
			$npassErr = '<p class="error"><b>Las contraseñas nuevas no coinciden</b></p>';
			$error = 1;
		} else {
			$npassword = test_input($_POST["npassword"]);
		}
		if (empty(test_input($_POST["cpassword"]))) {
			$cpassEmpty = '<p class="error"><b>La contraseña es obligatoria</b></p>';
			$error = 1;
		}
		if ($error != 1) {
			$oldpassword = substr($oldpassword, 0, 40);
			$npassword = substr($npassword, 0, 40);
			$oldpassword = mysqli_real_escape_string($conexion,$oldpassword);
			$npassword = mysqli_real_escape_string($conexion,$npassword);
			$npassword = password_hash($npassword, PASSWORD_DEFAULT);
			$username = $_SESSION["s_username"];
			$query = mysqli_query($conexion,"SELECT password,email FROM users WHERE username='$username'") or die(mysqli_error($conexion));
			$data = mysqli_fetch_array($query);
			if (password_verify($oldpassword, $data["password"])) {
				mysqli_query($conexion,"UPDATE users SET password='$npassword' WHERE username='$username'") or die(mysqli_error($conexion));
				$asunto = "Nueva contraseña de Mipagina.com";
				$cuerpo = "Estimado/a usuario: <br>\r\n
				<br>\r\n
				Usted acaba de cambiar su contraseña sactifactoriamente. <br>\r\n
				<br>\r\n
				Si usted no ha cambiado su contraseña, por favor visite el siguiente enlace inmediatamente: <br>\r\n
				<br>\r\n
				<a href='http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME'])."/pforgotpass.php'>
				http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME'])."/pforgotpass.php</a><br>\r\n
				<br>\r\n
				Saludos Cordiales, <br>\r\n
				Mipagina.com";
				$cabeceras = "MIME-version: 1.0<br>\r\n";
				$cabeceras .= "Content-type: text/html; charset=utf-8<br>\r\n";
				$cabeceras .= "From: Mipagina.com <webmaster@mipagina.com><br>\r\n";
				$email = $data["email"];
				mail($email,'=?utf-8?B?'.base64_encode($asunto).'?=',$cuerpo,$cabeceras);
				$_SESSION = array();
				session_unset();
				session_destroy();
				session_start();
				$_SESSION["msgerr"] =  "La contraseña ha sido actualiza de manera satisfactoria";
				unset($_POST);
				mysqli_free_result($query);
				mysqli_close($conexion);
				header("Location:index.php");
				exit();
			} else {
				$msgErr = "La contraseña anterior ingresada no coincide con la contraseña de la base de datos";
			}
			mysqli_free_result($query);
			mysqli_close($conexion);
		}
	}
?>
<!DOCTYPE html>
<html>
<head>
<title>Cambiar contraseña</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
<div class="pass">
<form action="<?=$_SERVER["SCRIPT_NAME"];?>" method="POST">
	<h2><b>Cambiar contraseña</b></font></h2>
	<i class="fa fa-user"></i> Nombre de usuario: <?=$_SESSION["s_username"];?>
	<label for="oldpassword"><i class="fa fa-key"></i> Contraseña actual</label>
	<input type="password" class="input-field" id="oldpassword" name="oldpassword" maxlength="40" placeholder="Contraseña actual" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,40}" title="Debe contener al menos un número, una letra mayúscula y una minúscula, y al menos 8 caracteres">
	<?=$passErr;?>
	<label for="npassword"><i class="fa fa-key"></i> Contraseña nueva</label>
	<input type="password" class="input-field" id="npassword" name="npassword" maxlength="40" placeholder="Contraseña nueva" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,40}" title="Debe contener al menos un número, una letra mayúscula y una minúscula, y al menos 8 caracteres">
	<?=$npassErr;?>
	<label for="cpassword"><i class="fa fa-key"></i> Repite contraseña</label>
	<input type="password" class="input-field" id="cpassword" name="cpassword" maxlength="40" placeholder="Repite contraseña" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,40}" title="Debe contener al menos un número, una letra mayúscula y una minúscula, y al menos 8 caracteres">
	<?=$cpassEmpty;?>
	<p><button type="reset" class="btn red"><b>Cancelar</b></button>&emsp;<button type="submit" class="btn green"><i class="fa fa-floppy-o"></i> <b>Guardar cambios</b></button></p>
</form>
<p><a href="index.php">Inicio</a> | <a href="plogout.php">Salir</a></p>
<br><br>
<?=$msgErr;?>
</div>
</body>
</html>";
<?php
} else {
	$_SESSION = array();
	session_unset();
	session_destroy();
	header("Location:index.php");
}
?>