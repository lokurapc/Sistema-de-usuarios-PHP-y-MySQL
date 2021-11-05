<?php
if (version_compare(phpversion(), '5.5.0', '<')) {
	exit("Necesita al menos PHP 5.5.0, su version actual de PHP es: ".PHP_VERSION);
}
require_once('config.php');
require_once('functions.php');
session_start();

$msgErr = $userErr = $passErr = $cpassEmpty = $emailErr = $capchaErr = "";
$username = $password = $cpassword = $codigo_seguridad = $email = "";
$domain = $img_aleatoria = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if (empty(test_input($_POST["username"]))) {
		$userErr = '<p class="error"><b>El usuario es obligatorio</b></p>';
		$error = 1;
	} elseif (strlen(test_input($_POST["username"])) <= 4) {
		$userErr = '<p class="error"><b>El usuario debe tener por lo menos 5 caracteres</b></p>';
		$error = 1;
	} else {
		$username = test_input($_POST["username"]);
	}
	if (empty(test_input($_POST["password"]))) {
		$passErr = '<p class="error"><b>La contraseña es obligatoria</b></p>';
		$error = 1;
	} elseif (strlen(test_input($_POST["password"])) < 8) {
		$passErr = '<p class="error"><b>La contraseña debe tener por lo menos 8 caracteres</b></p>';
		$error = 1;
	} elseif (test_input($_POST["password"])!= test_input($_POST["cpassword"])) {
		$passErr = '<p class="error"><b>Las contraseñas no coinciden</b></p>';
		$error = 1;
	} else {
		$password = test_input($_POST["password"]);
	}
	if (empty(test_input($_POST["cpassword"]))) {
		$cpassEmpty = '<p class="error"><b>La contraseña es obligatoria</b></p>';
		$error = 1;
	}
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
		$capchaErr = '<p class="error"><b>El captcha es obligatorio</b></p>';
		$error = 1;
	} elseif ($img_aleatoria != test_input($_POST["codigo_seguridad"])) {
		$capchaErr = '<p class="error"><b>Captcha incorrecto</b></p>';
		$error = 1;
	} else {
		$codigo_seguridad = test_input($_POST["codigo_seguridad"]);
	}
	if ($error != 1) {
		$username = substr($username, 0, 30);
		$password = substr($password, 0, 40);
		$email = substr($email, 0, 60);
		$username = mysqli_real_escape_string($conexion,$username);
		$password = mysqli_real_escape_string($conexion,$password);
		$password = password_hash($password, PASSWORD_DEFAULT);
		$email = mysqli_real_escape_string($conexion,$email);
		$checkuser = mysqli_query($conexion,"SELECT username FROM users WHERE username='$username'");
		$username_exist = mysqli_num_rows($checkuser);
		$checkemail = mysqli_query($conexion,"SELECT email FROM users WHERE email='$email'");
		$email_exist = mysqli_num_rows($checkemail);
		if ($email_exist>0|$username_exist>0) {
			$msgErr = "El nombre de usuario o la cuenta de correo ya estan en uso";
		} else {
			//aqui registra
			$fechadealta = time();
			$hash = generar_codigo(32);
			$codigo_confirmacion = hash("sha256",md5(str_shuffle(time().$hash.$_SERVER['HTTP_HOST'])));
			$query = "INSERT INTO users (username, password, email, fechadealta, confirmacion) VALUES('$username','$password','$email','$fechadealta','$codigo_confirmacion')";
			mysqli_query($conexion,$query) or die(mysqli_error($conexion));
			$asunto = "Bienvenido a Mipagina.com";
			$cuerpo = "Estimado/a usuario <br>\r\n
			Sus datos para entrar en su cuenta son los siguientes: <br>\r\n
			<br>\r\n
			Usuario: ".$username." <br>\r\n
			<br>\r\n
			Recuerde que la contraseña es sensible a mayúsculas/minúsculas. <br>\r\n
			<br>\r\n
			Para activar su cuenta por favor visite el siguiente enlace: <br>\r\n
			<br>\r\n
			<a href='http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?username=".$username."&code=".$codigo_confirmacion."'>Activar cuenta</a><br>\r\n
			<br>\r\n
			o copie y pegue el siguiente link: <br>\r\n
			<br>\r\n
			http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?username=".$username."&code=".$codigo_confirmacion."<br>\r\n
			<br>\r\n
			Este enlace caducará en 48 horas.<br>\r\n
			<br>\r\n
			Tenga en cuenta que debe guardar este correo electronico como registro. <br>\r\n
			<br>\r\n
			Saludos Cordiales, <br>\r\n
			Mipagina.com";
			$cabeceras = "MIME-version: 1.0<br>\r\n";
			$cabeceras .= "Content-type: text/html; charset=utf-8<br>\r\n";
			$cabeceras .= "From: Mipagina.com <webmaster@mipagina.com><br>\r\n";
			mail($email,'=?utf-8?B?'.base64_encode($asunto).'?=',$cuerpo,$cabeceras);
			$_SESSION["msgerr"] = "El usuario ".$username." ha sido registrado de manera satisfactoria. <br>Revise su correo electronico ".$email." para activar su cuenta";
			mysqli_close($conexion);
			unset($_POST);
			header("Location:index.php");
		}
	}
} elseif (isset($_GET["username"]) && isset($_GET["code"])) {
	$username = test_input($_GET["username"]);
	$username = mysqli_real_escape_string($conexion,$username);
	$code = test_input($_GET["code"]);
	if ($code == 1) {
		//$_SESSION["msgerr"] = "Error de comprobacion para activar su cuenta, intente copiando el enlace en su navegador.<br> O comniquese con el adminstrador. (1)";
		header("Location:index.php");
	} else {
		$query = mysqli_query($conexion,"SELECT username,confirmacion,fechadealta FROM users WHERE username='$username'") or die(mysqli_error($conexion));
		$data = mysqli_fetch_array($query);
		$tiempolimite = $data["fechadealta"] + (3600 * 24 * 2); // 48 horas
		unset($_GET);
		if (mysqli_num_rows($query) == 1 && $data["confirmacion"] === $code && time() < $tiempolimite) {
			mysqli_query($conexion,"UPDATE users SET confirmacion='1' WHERE username='$username' AND confirmacion='$code'") or die(mysqli_error($conexion));
			$_SESSION["msgerr"] = "Su cuenta ha sido activada correctamente.";
			header("Location:index.php");
		} elseif (mysqli_num_rows($query) == 1 && $data["confirmacion"] == 1) {
			//$_SESSION["msgerr"] = "Su cuenta ya se encontraba activada.";
			header("Location:index.php");
		} else {
			$_SESSION["msgerr"] = "Error de comprobación para activar su cuenta, intente copiando el enlace en su navegador.<br> O comniquese con el adminstrador.";
			header("Location:index.php");
		}
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Registrar nuevo usuario</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
<div class="register">
<form action="<?=$_SERVER["SCRIPT_NAME"];?>" method="POST">
	<h2><b>Registrar nuevo usuario</b></h2>
	<label for="username"><i class="fa fa-user"></i> Usuario:</label>
	<input class="input-field" type="text" id="username" name="username" maxlength="40" placeholder="Usuario" value="<?=$username;?>" pattern="[a-zA-Z0-9][A-Za-z0-9._-]{4,40}" title="Solo letras y números. Cantidad mínima: 5, máximo: 40">
	<?=$userErr;?>
	<label for="password"><i class="fa fa-key"></i> Contraseña:</label>
	<input class="input-field" type="password" id="password" name="password" maxlength="40" placeholder="Contraseña" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,40}" title="Debe contener al menos un número, una letra mayúscula y una minúscula, y al menos 8 o más caracteres">
	<?=$passErr;?>
	<label for="cpassword"><i class="fa fa-key"></i> Repite contraseña:</label>
	<input class="input-field" type="password" id="cpassword" name="cpassword" maxlength="40" placeholder="Repite contraseña" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,40}" title="Debe contener al menos un número, una letra mayúscula y una minúscula, y al menos 8 o más caracteres">
	<?=$cpassEmpty;?>
	<label for="email"><i class="fa fa-envelope"></i> Email:</label>
	<input class="input-field" type="text" id="email" name="email" maxlength="50" placeholder="E-mail" value="<?=$email;?>" pattern="^([a-z0-9]{1,}[a-z0-9._+-]{0,}@(([a-z0-9]{1,}\.|[a-z0-9]{1,}[a-z0-9-]{1,}[a-z0-9]{1,}\.){1,})([a-z]{2,}))$" title="Debe ser un formato de email valido">
	<?=$emailErr;?>
	<p><img src="img.php" id="captcha"> <i class="fa fa-refresh refresh-captcha"></i></p>
	<p>Ingrese el código que se muestra en la imagen:<br>
	(los caracteres diferencian mayúsculas de minúsculas)<br>
	<input class="input-captcha" type="text" name="codigo_seguridad" maxlength="12" autocomplete="off"><br>
	<?=$capchaErr;?></p>
	<p>Al registrarme, afirmo que he leído y acepto los Términos y Condiciones<br>y las Políticas de Privacidad del sitio.</p>
	<p><button type="reset" class="btn red"><b>Cancelar</b></button>&emsp;<button type="submit" class="btn green"><i class="fa fa-user-plus"></i> <b>Registrarme</b></button></p>
	<p>¿ya tenes cuenta? <a href="index.php">Iniciar Session aquí</a></p>
</form>
<?php
if ($msgErr != "") {
	echo "<p><b>".$msgErr."</b></p>";
	$_SESSION = array();
	session_unset();
	session_destroy();
}
?>
</div>
<script type="text/javascript" src="script.js"></script>
</body>
</html>