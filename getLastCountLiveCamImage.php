<?php
include 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);


if (isset($_GET['camname']))
	$camname=$_GET['camname'];
else {
	echo("Parameter camname ist empty");
	header("HTTP/1.0 400 Bad Request");
die;
}

if (isset($_GET['nr']))
	$nr=intval($_GET["nr"]);
else 
	$nr=0;

if (isset($_GET['interval']))
	$interval=intval($_GET["interval"]);
else
	$interval=10;
	

$filter=".";

$imgPath=Constants::getCameras()[$camname]["path"];

	$path =Constants::IMAGE_ROOT_PATH.$imgPath;
	$directory = dir($path);
	$images = array();
	while ($file = $directory->read()) {
		if (in_array(strtolower(substr($file, -4)), array(".jpg",".gif",".png")) && strstr($file,$filter) ) {
			$images[filectime(Constants::IMAGE_ROOT_PATH.$imgPath.$file)]=$file;
		}
	}
	if (sizeof($images)>0) {
		ksort($images);
		$im = imagecreatefromjpeg(Constants::IMAGE_ROOT_PATH.$imgPath.array_values($images)[sizeof($images)-$nr*$interval-1]);
		//if ($im) {
			Header ("Content-type: image/jpg");
			ImageJpeg ($im);
			ImageDestroy ($im);
		//}
	}
	$directory->close();

?>