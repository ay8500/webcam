<?php
/**
 * This script will return the list of pictures for a camera from the same day and type
 * Vers: 1.2.0
 */
include 'config.php';
include_once 'bifi.class.php';
include_once 'cameraTools.php';

header('Content-Type: application/json');


if (isset($_GET['camname']))
    $camname=$_GET['camname'];
else {
    echo("Parameter camname ist empty");
    header("HTTP/1.0 400 Bad Request");
    die;
}


if (isset($_GET['type']))
    $type=$_GET['type'];
else
    $type="";

if (isset($_GET['day']) && $_GET['day']!="" )
    $day=new DateTime($_GET['day']);
else
    $day=new DateTime();

//if parameter deleted is set to a random value the number of the deleted files in the picture archive will be returned
if (isset($_GET['deleted']) && $_GET['deleted']!="" )
    $deleted=true;
else
    $deleted=false;

$camera = isset(Constants::getCameras()[$camname])?Constants::getCameras()[$camname]:null;
$images_array= array();
if($camera==null || (!isUserRoot()  && !isUserView() && !$camera["webcam"])) {
    echo(json_encode($images_array));
    die();
} else {
    $filter=$type;
    if ($camera["zip"]) {
        $path=Constants::IMAGE_ROOT_PATH.$camera["path"];
        $zip = new BiFi();
        $fzip=$path."cam".date_format($day, 'Ymd').".zip";
        if ($zip->open($fzip)) {
			$images_array = $zip->getArchiveFileCount($filter,true);
        }
        if ($zip->numFiles>0)
	        sort($images_array);
    } else {
        $path=Constants::IMAGE_ROOT_PATH.$camera["path"];
        $files = getFileList($path,Constants::getCameras()[$camname]["patternRegEx"]);
        foreach ($files as $file) {
            if (($filter=="" || strstr($file,$filter)) &&
                $day->format("Ymd")==(new DateTime())->setTimestamp($file["lastmod"] )->format("Ymd"))
                $images_array[]=Constants::getCameras()[$camname]["path"].str_replace($path,"",$file["name"]);
        }
	    rsort($images_array);
    }

    if($deleted)
        //Return how many file are have a deleted flag
        echo(json_encode($zip->numDeletedFiles));
    else
        //Return the file list
        echo(json_encode($images_array));
}
