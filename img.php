<?php
require_once('functions.php');

$ancho=150;
$alto=45;

$im = imagecreatefromgif("back-img.gif");

$color[0]=imagecolorallocate($im,0,0,0); //negro
$color[1]=imagecolorallocate($im,0,0,255); //azul
$color[2]=imagecolorallocate($im,0,128,0); //verde
$color[3]=imagecolorallocate($im,255,0,0); //rojo
$color[4]=imagecolorallocate($im,255,127,0); //amarillo
$color[5]=imagecolorallocate($im,128,0,128); //violeta
$color[6]=imagecolorallocate($im,127,127,127); //gris

//Elipses a la azar
for ($i=0;$i<5;$i++) {
	$x1=rand(0,$ancho);
	$y1=rand(0,$alto);
	$radio=($ancho/2)-($i*2);
	imageellipse($im,$x1,$y1,$radio,$radio,$color[rand(0,6)]);
}

//rectagunlos a la azar
for ($i=0;$i<5;$i++) {
	$x1=rand(0,$ancho);
	$y1=rand(0,$alto);
	$x2=rand(0,$ancho);
	$y2=rand(0,$alto);
	imagerectangle($im,$x1,$y1,$x2,$y2,$color[rand(0,6)]);
}

//lineas al azar
for ($i=0;$i<=5;$i++) {
	$x1=rand(0,$ancho);
	$y1=rand(0,$alto);
	$x2=rand(0,$ancho);
	$y2=rand(0,$alto);
	imageline($im,$x1,$y1,$x2,$y2,$color[rand(0,6)]);
}

session_start();
$captcha_string = generar_codigo(6);
$_SESSION['imgaleatoria'] = $captcha_string;

for($i = 0; $i < 6; $i++) {
	$letter_space = 22;
	$initial = 10;
	imagettftext($im, 20, rand(-10, 10), $initial + $i*$letter_space, rand(25, 40), $color[rand(0,6)], "caveatbrush.ttf", $captcha_string[$i]);
}

header("Content-type: image/gif");
imagegif($im);
imagedestroy($im);
?>