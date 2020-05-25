<?php
/**
 * Script to delete one image from file system
 * Vers. 1.2.0
*/
include_once 'config.class.php';
include_once Config::$lpfw.'ltools.php';
include_once Config::$lpfw.'logger.class.php';

header('Content-Type: application/json');
\maierlabs\lpfw\Logger::setLoggerType(\maierlabs\lpfw\LoggerType::file, Config::jc()->IMAGE_ROOT_PATH.'log');

\maierlabs\lpfw\Logger::_(getParam("text"),\maierlabs\lpfw\LoggerLevel::info);

?>