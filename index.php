<?php
if (version_compare(phpversion(), '5.5.0', '<')) {
	exit("Necesita al menos PHP 5.5.0, su version actual de PHP es: ".PHP_VERSION);
}
require_once('config.php');
require_once('functions.php');
session_start();

$msgErr = $userEmpty = $passEmpty = $error = "";
$username = $password = $remember = $hash = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if (empty(test_input($_POST["username"]))) {
		$userEmpty = '<p class="error"><b>El usuario es obligatorio</b></p>';
		$error = 1;
	} else {
		$username = test_input($_POST["username"]);
	}
	if (empty(test_input($_POST["password"]))) {
		$passEmpty = '<p class="error"><b>La contraseña es obligatoria</b></p>';
		$error = 1;
	} else {
		$password = test_input($_POST["password"]);
	}
	if ($error != 1) {
		$username = substr($username, 0, 30);
		$password = substr($password, 0, 40);
		$username = mysqli_real_escape_string($conexion,$username);
		$password = mysqli_real_escape_string($conexion,$password);
		$remember = test_input($_POST["remember"]);
		$query = mysqli_query($conexion,"SELECT * FROM users WHERE username='$username'") or die(mysqli_error($conexion));
		$data = mysqli_fetch_array($query);
		if (mysqli_num_rows($query) == 1 && $data["confirmacion"] == 1) {
			$time = time();
			$ultimoingreso60 = $data["ultimoingreso"] + 60;
			if ($data["intentos"] > 20) {
				$msgErr = "El usuario ".$username." fue bloqueado permanentemente por reiterados intentos fallidos de su contraseña, comuniquese con el administrador";
			} elseif ($data["intentos"] <= 5 || ($data["intentos"] >= 5 && $time > $ultimoingreso60)) {
				if (password_verify($password, $data["password"])) {
					if ($remember) {
						$hash = generar_codigo(32);
						$hash = hash("sha256",md5(str_shuffle(time().$hash.$_SERVER['HTTP_HOST'])));
						setcookie("username", $username, time() + (3600 * 24 * 30), "/","", 0); // 30 dias
						setcookie("id_hash", $hash, time() + (3600 * 24 * 30), "/","", 0); // 30 dias
						mysqli_query($conexion,"UPDATE users SET intentos=0, ultimoingreso=$time, autologinhash='$hash', fechaautologin=$time WHERE username='$username'") or die(mysqli_error($conexion));
					} else {
						mysqli_query($conexion,"UPDATE users SET intentos=0, ultimoingreso=$time WHERE username='$username'") or die(mysqli_error($conexion));
					}
					$data["username"];
					$_SESSION["s_username"] = $data["username"];
					unset($_POST);
				} else {
					mysqli_query($conexion,"UPDATE users SET intentos=intentos+1, ultimoingreso=$time WHERE username='$username'") or die(mysqli_error($conexion));
					$intentos = $data["intentos"]+1;
					$msgErr = '<p class="error"><b>Usuario o contraseña incorrecto (p); intento '.$intentos.'</b></p>';
				}
			} else {
				mysqli_query($conexion,"UPDATE users SET ultimoingreso=$time WHERE username='$username'") or die(mysqli_error($conexion));
				$msgErr .= "<p><b>El usuario ".$username." a intentado entrar demasiadas veces erroneamente, debe esperar 1 minuto para volver a entrar con este usuario.</b></p>";
			}
		} elseif (mysqli_num_rows($query) == 1 && $data["confirmacion"] != 1) {
			$msgErr = "<p><b>Debe activar su cuenta con el enlace enviado a su correo electronico.</b></p>";
		} else {
			$msgErr = '<p class="error"><b>Usuario o contraseña incorrecto (u)</b></p>';
		}
	}
	mysqli_free_result($query);
	mysqli_close($conexion);
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Ingreso</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
<?php
logedIn($conexion);
if (isset($_SESSION) && isset($_SESSION["s_username"])) {
	echo "<p>Bienvenido: ".$_SESSION['s_username'].", gracias por la visita!</p>
	<p><a href='pagina1.php'>Enlace 1</a></p>
	<p><a href='pagina2.php'>Enlace 2</a></p>
	<p><a href='pagina3.php'>Enlace 3</a></p>
	<p><a href='pcambiarpass.php'>Cambiar contraseña</a></p>
	<p><a href='plogout.php'>Salir</a></p>";
} else {
?>
<div class="login">
<form action="<?=$_SERVER["SCRIPT_NAME"];?>" method="POST">
	<h2><b>Ingresar al sistema</b></h2>
	<div class="input-container">
		<i class="fa fa-user icon"></i>
		<input class="input-field" type="text" placeholder="Usuario" name="username" maxlength="40" pattern="[a-zA-Z0-9][A-Za-z0-9._-]{4,40}" value="<?=$username;?>" title="Solo letras y números. Cantidad mínima: 5, máximo: 40">
	</div>
	<?=$userEmpty;?>
	<div class="input-container">
		<i class="fa fa-key icon"></i>
		<input class="input-field" type="password" placeholder="Contraseña" name="password" id="password" maxlength="40">
		<i class="fa fa-eye-slash iconeye" id="togglePassword"></i>
	</div>
	<?=$passEmpty;?>
	<p><input type="checkbox" name="remember" id="remember">Recordarme</p>
	<p><button type="submit" class="btn blue"><i class="fa fa-sign-in"></i> <b>Ingresar</b></button></p>
	<p><a href="pregister.php">Registrarme</a> | <a href="pforgotpass.php">Olvidé mi contraseña</a></p>
</form>
<?php
if (isset($_SESSION["msgerr"])) {
	echo "<p><b>".$_SESSION['msgerr']."</b></p>";
	$_SESSION = array();
	session_unset();
	session_destroy();
} elseif ($msgErr != "") {
	echo $msgErr;
}
?>
</div>
<?php
}
?>
<script type="text/javascript" src="script.js"></script>
</body>
</html>