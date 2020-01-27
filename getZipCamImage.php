<?php
/**
 * This script will return a picture from the archive
 * Vers: 1.2.0
 */

include 'config.php';
include_once 'bifi.class.php';

if (isset($_GET['camname']))
    $camname=$_GET['camname'];
else {
    echo("Parameter camname ist empty");
    header("HTTP/1.0 400 Bad Request");
    die;
}
if (isset($_GET['imagename']))
    $imagename=$_GET['imagename'];
else {
    echo("Parameter imagename ist empty");
    header("HTTP/1.0 400 Bad Request");
    die;
}
if (isset($_GET['date']))
    $imagedate=$_GET['date'];
else {
    echo("Parameter date ist empty");
    header("HTTP/1.0 400 Bad Request");
    die;
}

if($imagedate=="undefined" || $imagename=="undefined") {
    $im=imagecreatefromjpeg("password.jpg");
    Header ("Content-type: image/jpg");
    ImageJpeg ($im);
    ImageDestroy ($im);
    return;
}

$camera = Constants::getCameras()[$camname];
$imgPath=$camera["path"];
$path =Constants::IMAGE_ROOT_PATH.$imgPath;
$fzip=$path."cam".$imagedate.".zip";
$zip = new BiFi();
if ($zip->open($fzip)) {
    $is = $zip->getFromName($imagename);
    $zip->close();unset($zip);

    $im = imagecreatefromstring($is);
    Header ("Content-type: image/jpg");
    ImageJpeg ($im);
    ImageDestroy ($im);
}

?>