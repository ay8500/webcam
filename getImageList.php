<?php
include 'config.php';

header('Content-Type: application/json');


if (isset($_GET['camname']))
	$camname=$_GET['camname'];
else {
	header("HTTP/1.0 400 Bad Request");
	die;
}


if (isset($_GET['type']))
	$type=$_GET['type'];
else
	$type=Constants::SNAP;

if (isset($_GET['day']) && $_GET['day']!="" )
	$day=new DateTime($_GET['day']);
else
	$day=new DateTime();


$images_array= array();

if(isUserOk()) {
	$filter=$type.date_format($day, 'Ymd');
	$ip=Constants::IMAGE_PATH();
	$path="./".$ip[$camname];
	$directory = dir($path);	
	$idx=0;
	while ($file = $directory->read()) {
		if (in_array(strtolower(substr($file, -4)), array(".jpg",".gif",".png")) &&
		  strstr($file,$filter) ) {
			$images_array[$idx] = $path.$file;	
			$idx++;
		}
		
	}
	$directory->close();
	rsort($images_array);
}
else {
	$images_array[0] = "./password.jpg";
}

echo(json_encode($images_array));

function isUserOk() {
	return isset($_COOKIE["password"]) && ( $_COOKIE["password"]==Constants::PASSW_ROOT || $_COOKIE["password"]==Constants::PASSW_VIEW );
}
?>