<?php
/**
 * Script to delete one image from file system
 * Vers. 1.2.0
*/
include 'config.php';
include_once 'config.class.php';
include_once Config::$lpfw.'logger.class.php';

header('Content-Type: application/json');
\maierlabs\lpfw\Logger::setLoggerType(\maierlabs\lpfw\LoggerType::file, Constants::IMAGE_ROOT_PATH.'log');

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

$filename=str_replace(Constants::IMAGE_URL(), Constants::IMAGE_ROOT_PATH, $filename);

if (unlink($filename)) {
	header("HTTP/1.0 200 OK");
	echo(json_encode("Ok"));
    \maierlabs\lpfw\Logger::_("Delete file:".$filename,loggerLevel::info);
} else {
	echo("File:".$filename." not deleted!");
	header("HTTP/1.0 400 Bad Request");
	echo(json_encode("Error"));
}
	
?>