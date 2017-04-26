<?php
include 'config.php';

header('Content-Type: application/json');

if (isset($_GET['filename']))
	$filename=$_GET['filename'];
else {
	header("HTTP/1.0 400 Bad Request");
	die("Filename missing");
}

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



if (unlink($filename)) {
	header("HTTP/1.0 200 OK");
	//logger("delete file File:".$filename,loggerLevel::info);
	echo(json_encode("Ok"));
} else {
	header("HTTP/1.0 400 Bad Request");
	//logger("delete file File:".$filename,loggerLevel::error);
	echo(json_encode("Error"));
}
	


?>