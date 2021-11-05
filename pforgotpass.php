<?php
if (version_compare(phpversion(), '5.5.0', '<')) {
	exit("Necesita al menos PHP 5.5.0, su version actual de PHP es: ".PHP_VERSION);
}
require_once('config.php');
require_once('functions.php');
session_start();

$msgErr = $userErr = $emailErr = $capchaErr = "";
$username = $password = $codigo_seguridad = $email = "";
$domain = $img_aleatoria = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if (empty(test_input($_POST["email"]))) {
		$emailErr = '<p class="error"><b>El email es obligatorio</b></p>';
		$error = 1;
	} elseif (filter_var(test_input($_POST["email"]),FILTER_VALIDATE_EMAIL)) {
		$email = test_input($_POST["email"]);
		$domain = end(explode("@", $email));
		if (!checkdnsrr($domain,"MX")) {
			$emailErr = '<p class="error"><b>El email debe ser una dirección valida (dominio)</b></p>';
			$error = 1;
		}
	} else {
		$email = test_input($_POST["email"]);
		$emailErr = '<p class="error"><b>El email debe ser una dirección valida</b></p>';
		$error = 1;
	}
	$img_aleatoria = trim($_SESSION["imgaleatoria"]);
	if (empty(test_input($_POST["codigo_seguridad"]))) {
		$capchaErr = '<p class="error"><b>El captcha es obligatorio</b><p>';
		$error = 1;
	} elseif ($img_aleatoria != test_input($_POST["codigo_seguridad"])) {
		$capchaErr = '<p class="error"><b>Captcha incorrecto</b></p>';
		$error = 1;
	} else {
		$codigo_seguridad = test_input($_POST["codigo_seguridad"]);
	}
	if ($error != 1) {
		$email = substr($email, 0, 60);
		$email = mysqli_real_escape_string($conexion,$email);
		$query = mysqli_query($conexion,"SELECT email FROM users WHERE email='$email'") or die(mysqli_error($conexion));
		$data = mysqli_fetch_array($query);
		$hash = generar_codigo(32);
		$codigo_confirmacion = hash("sha256",md5(str_shuffle(time().$hash.$_SERVER['HTTP_HOST'])));
		if (mysqli_num_rows($query) == 1 && $data["email"] == $email) {
			$asunto = "Generar contraseña nueva de Mipagina.com";
			$cuerpo = "Estimado/a usuario: <br>\r\n
			<br>\r\n
			Para generar una contraseña nueva por favor visite el siguiente enlace: <br>\r\n
			<br>\r\n
			<a href='http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?email=".$email."&code=".$codigo_confirmacion."'>
			http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?email=".$email."&code=".$codigo_confirmacion."</a>	<br>\r\n
			<br>\r\n
			Este enlace caducará en 4 horas.<br>\r\n
			<br>\r\n
			Si no has solicitado un cambio de contraseña, puedes ignorar o eliminar este e-mail. <br>\r\n
			<br>\r\n
			Saludos cordiales, <br>\r\n
			Mipagina.com";
			$cabeceras = "MIME-version: 1.0<br>\r\n";
			$cabeceras .= "Content-type: text/html; charset=utf-8<br>\r\n";
			$cabeceras .= "From: Mipagina.com <webmaster@mipagina.com><br>\r\n";
			mail($email,'=?utf-8?B?'.base64_encode($asunto).'?=',$cuerpo,$cabeceras);
			$time = time();
			mysqli_query($conexion,"UPDATE users SET resetpassword='$codigo_confirmacion', fechareset=$time WHERE email='$email'") or die(mysqli_error($conexion));
			$_SESSION = array();
			$_SESSION["msgerr"] = "Se enviado un correo electrónico con los pasos a seguir";
			mysqli_free_result($query);
			mysqli_close($conexion);
			unset($_POST);
			header("Location:index.php");
		} else {
			$msgErr = '<p class="error"><b>El email no coincide con la base de datos</b></p>';
			mysqli_free_result($query);
			mysqli_close($conexion);
		}
	}
} elseif (isset($_GET["email"]) && isset($_GET["code"])) {
	$email = test_input($_GET["email"]);
	$email = mysqli_real_escape_string($conexion,$email);
	$code = test_input($_GET["code"]);
	$query = mysqli_query($conexion,"SELECT resetpassword,fechareset FROM users WHERE email='$email'") or die(mysqli_error($conexion));
	$data = mysqli_fetch_array($query);
	$tiempolimite = $data["fechareset"] + (3600 * 4); // 4 horas
	unset($_GET);
	if (mysqli_num_rows($query) == 1 && $data["resetpassword"] === $code && time() < $tiempolimite) {
		$password = generar_codigo(12);
		$npassword = password_hash($password, PASSWORD_DEFAULT);
		$asunto = "Nueva contraseña de Mipagina.com";
		$cuerpo = "Estimado/a usuario: <br>\r\n
		<br>\r\n
		Su nueva contraseña es: <b>".$password."</b> <br>\r\n
		<br>\r\n
		Saludos Cordiales, <br>\r\n
		Mipagina.com";
		$cabeceras = "MIME-version: 1.0<br>\r\n";
		$cabeceras .= "Content-type: text/html; charset=utf-8<br>\r\n";
		$cabeceras .= "From: Mipagina.com <webmaster@mipagina.com><br>\r\n";
		mail($email,'=?utf-8?B?'.base64_encode($asunto).'?=',$cuerpo,$cabeceras);
		mysqli_query($conexion,"UPDATE users SET password='$npassword', resetpassword=NULL,fechareset=NULL WHERE email='$email'") or die(mysqli_error($conexion));
		$_SESSION["msgerr"] = "La contraseña nueva ha sido generega";
		header("Location:index.php");
	} else {
		$_SESSION["msgerr"] = "Error de comprobación, intente copiando el enlace en su navegador.<br> O comniquese con el adminstrador.";
		header("Location:index.php");
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Generar nueva contraseña</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
<div class="pass">
<form action="<?=$_SERVER["SCRIPT_NAME"];?>" method="POST">
	<h2><b>Generar nueva contraseña</b></h2>
	<p>Para restablecer tu contraseña, introduce la dirección de correo electrónico que utilizaste para registrarte.<br>
	El sistema te enviará un email informando los pasos a seguir para restablecer tu contraseña.</p>
	<div class="input-container">
		<i class="fa fa-envelope icon"></i>
		<input class="input-field" type="text" placeholder="Email" name="email" maxlength="50" value="<?=$email;?>" pattern="^([a-z0-9]{1,}[a-z0-9._+-]{0,}@(([a-z0-9]{1,}\.|[a-z0-9]{1,}[a-z0-9-]{1,}[a-z0-9]{1,}\.){1,})([a-z]{2,}))$" title="Debe ser un formato de email valido">
	</div>
	<?=$emailErr;?>
	<p><img src="img.php" id="captcha" id="captcha"> <i class="fa fa-refresh refresh-captcha"></i></p>
	<p>Ingrese el código que se muestra en la imagen:<br>
	(los caracteres diferencian mayúsculas de minúsculas)<br>
	<input class="input-captcha" type="text" name="codigo_seguridad" maxlength="12" autocomplete="off"><br>
	<?=$capchaErr;?></p>
	<p><button type="reset" class="btn red"><b>Cancelar</b></button>&emsp;<button type="submit" class="btn green"><i class="fa fa-paper-plane"></i> <b>Enviar</b></button></p>
	<p class="error">Algunos proveedores de correo (como Hotmail, Yahoo, Gmail, entre otros) pueden considerar este tipo de avisos como correo no deseado, si no encuentras el email en tu bandeja de entrada no olvide verificar tu carpeta de correo no deseado (spam).</p>
</form>
<?=$msgErr;?>
</div>
<script type="text/javascript" src="script.js"></script>
</body>
</html>