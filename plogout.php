<?php
session_start();
if (isset($_SESSION) && isset($_SESSION["s_username"])) {
	$_SESSION = array();
	session_unset();
	session_destroy();
}
setcookie("username", "", time() - 3600, "/","", 0);
setcookie("id_hash", "", time() - 3600, "/","", 0);
header("Location:index.php");
?>