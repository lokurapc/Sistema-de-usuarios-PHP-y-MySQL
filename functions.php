<?php
//Previene que el usuario entre en este archivo directamente
if ($_SERVER['SCRIPT_FILENAME'] == __FILE__) {
	header("Location:index.php");
	exit();
}

function test_input($data) {
	$data = trim($data);
	$data = htmlentities($data, ENT_QUOTES | ENT_IGNORE, "UTF-8");
	$search = array("%", chr(92), chr(96)); //92 "\", 96 "`"
	$replace = array("&#37;", "&#92;", "&#96;");
	$data = str_replace($search, $replace, $data);
	return $data;
}

function generar_codigo($long) {
	$cadenatxt = "";
	$pcstr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
		for ($i=0;$i<$long;$i++) {
			$caracter = substr($pcstr,mt_rand(0,61),1);
			$cadenatxt .= $caracter;
		}
	return $cadenatxt;
}

function logedIn($conexion) {
	if ($_COOKIE["username"] != "" && $_COOKIE["id_hash"] != "") {
		$hash = test_input($_COOKIE["id_hash"]);
		$query = mysqli_query($conexion,"SELECT username, autologinhash, fechaautologin FROM users WHERE autologinhash='$hash'") or die(mysqli_error($conexion));
		$data = mysqli_fetch_row($query);
		$tiempolimite = $data["fechaautologin"] + (3600 * 24 * 30); // 30 dias
		if ($_COOKIE["username"] === $data[0] && $_COOKIE["id_hash"] === $data[1] && time() < $tiempolimite) {
			$_SESSION["s_username"] = $data[0];
		}
	}
}
?>