<?php
/**
 * Script to save settings in config.json
 * Vers. 1.2.0
 */
include_once 'config.class.php';
include_once Config::$lpfw.'ltools.php';
include_once Config::$lpfw.'logger.class.php';

header('Content-Type: application/json');
\maierlabs\lpfw\Logger::setLoggerType(\maierlabs\lpfw\LoggerType::file, Config::jc()->IMAGE_ROOT_PATH.'log');

if (isset($_GET['cameras']) || isset($_GET['newpassword'])) {
    $cameras = htmlspecialchars_decode(getParam('cameras'));
    $cameras = json_decode($cameras);
    if (getParam('newpassword','')!='')
        $newpassword = md5(getParam('newpassword'));
    else
        $newpassword = null;
} else {
    header("HTTP/1.0 400 Bad Request");
    die("Invalid parameters");
}

if (!Config::isUserView() && !Config::isUserRoot()) {
    header("HTTP/1.0 400 Bad Request");
    die ("Wrong Password");
}


$json = new stdClass();
$json->cameras =$cameras; $json->newpassword=$newpassword;
$json->root = Config::isUserRoot();$json->view=Config::isUserView();

$user ="";
if (Config::isUserRoot()) $user="R";
if (Config::isUserView()) $user="W";

if (Config::saveConfigJson($cameras,$newpassword)) {
    header("HTTP/1.0 200 OK");
    \maierlabs\lpfw\Logger::_("Save settings\tType\tCam\tUser:".$user , \maierlabs\lpfw\LoggerLevel::info);
    echo(json_encode($json));
} else {
    \maierlabs\lpfw\Logger::_("Save settings\tType\tCam\tUser:".$user , \maierlabs\lpfw\LoggerLevel::error);
    header("HTTP/1.0 400 Bad Request");
    echo("Error saving settings");
}

?>