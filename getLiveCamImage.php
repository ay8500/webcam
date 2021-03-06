<?php
/**
 * This script will return the latest camera picture
 * Vers: 1.2.0
 */

include 'config.class.php';
include_once 'bifi.class.php';


if (isset($_GET['camname'])) {
    $camname=$_GET['camname'];
    if (!isset(Config::ja()["cameras"][$camname]) || !Config::ja()["cameras"][$camname]["webcam"]) {
        header("HTTP/1.0 400 Bad Request");
        echo("Cam not allowed as webcam!");
        die();
    }
} else {
    header("HTTP/1.0 400 Bad Request");
    echo("Parameter camname ist empty");
    die();
}

$filter=".";

$imgPath=Config::ja()["cameras"][$camname]["path"];

$path =Config::jc()->IMAGE_ROOT_PATH.$imgPath;
$directory = dir($path);
$latest_ctime = 0;
$latest_filename = '';
if(Config::ja()["cameras"][$camname]["zip"]) {
    while ($file = $directory->read()) {
        if (strtolower(substr($file, -4))===".bfi" && intval(filectime($path.$file)) >= intval($latest_ctime)) {
            $latest_ctime = intval(filectime($path.$file));
            $latest_filename = $file;
        }
    }
    //newest zip file found
    if ($latest_filename!="") {
        $zip = new BiFi();
        $fzip=$path.substr($latest_filename,0,strlen($latest_filename)-4);
        $zip->open($fzip);
        $item=$zip->statIndex($zip->numFiles-1);
        $is = $zip->getFromName(key($item));
        $zip->close();unset($zip);

        $im = imagecreatefromstring($is);
        Header ("Content-type: image/jpg");
        ImageJpeg ($im);
        ImageDestroy ($im);
    }
} else {
    while ($file = $directory->read()) {
        if (in_array(strtolower(substr($file, -4)), array(".jpg", ".gif", ".png")) && strstr($file, $filter)) {
            $akt_time = intval(filemtime(Config::jc()->IMAGE_ROOT_PATH .Config::ja()["cameras"][$camname]["path"] . $file));
            if ($akt_time > $latest_ctime) {
                $latest_ctime = $akt_time;
                $latest_filename = $file;
            }
        }
    }
    if ($latest_filename != "") {
        $im = imagecreatefromjpeg(Config::jc()->IMAGE_ROOT_PATH . Config::ja()["cameras"][$camname]["path"] . $latest_filename);
        //if ($im) {
        Header("Content-type: image/jpg");
        ImageJpeg($im);
        ImageDestroy($im);
        //}
    }
    $directory->close();
}
?>