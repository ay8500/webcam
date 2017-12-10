<?php
include 'config.php';
include_once 'bifi.class.php';

header('Content-Type: application/json');

if (isset($_GET['filename']))
	$filename=$_GET['filename'];
else {
	header("HTTP/1.0 400 Bad Request");
	die("Filename missing");
}

if (isset($_GET['camname']))
	$camname=$_GET['camname'];
else {
	echo("Parameter camname ist empty");
	header("HTTP/1.0 400 Bad Request");
	die;
}

if (isset($_GET['day']) && $_GET['day']!="" )
	$day=new DateTime($_GET['day']);
else
	$day=new DateTime();

if (isset($_GET['password']))
	$password=$_GET['password'];
else {
	header("HTTP/1.0 400 Bad Request");
	die("Password required");
}

if ($password!=Constants::PASSW_ROOT) {
	header("HTTP/1.0 400 Bad Request");
	die ("Wrong Password");
}

$ip=Constants::IMAGE_PATH();
$path=Constants::IMAGE_ROOT_PATH.$ip[$camname];

$zip = new BiFi();
$fzip=$path."cam".date_format($day, 'Ymd').".zip";
if ($zip->open($fzip)) {
	if ($zip->deleteName($filename)) {
		echo(json_encode("Ok"));			
	} else {
		header("HTTP/1.0 400 Bad Request");
		echo(json_encode("Error: file:".$filename." not found!"));
	}
	$zip->close();
} else {
	header("HTTP/1.0 400 Bad Request");
	echo(json_encode("Error: file:".$filename." not deleted! Zip file:".$fzip. "not found."));
}
	


?>