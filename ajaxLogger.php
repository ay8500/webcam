<?php
/**
 * Script to log arround
 * Vers. 1.1.0
 * Created by MaierLabs
*/
include_once 'config.class.php';
include_once Config::$lpfw.'ltools.php';
include_once Config::$lpfw.'logger.class.php';

header('Content-Type: application/json');
\maierlabs\lpfw\Logger::setLoggerType(\maierlabs\lpfw\LoggerType::file, Config::jc()->IMAGE_ROOT_PATH.'log');

$text =getParam("text");
$user ="";
if (Config::isUserRoot()) $user="R";
if (Config::isUserView()) $user="W";
$text .="\tType:".getParam("type","")."\tCam:".getParam("cam","")."\tUser:".$user;
\maierlabs\lpfw\Logger::_($text,\maierlabs\lpfw\LoggerLevel::info);

?>