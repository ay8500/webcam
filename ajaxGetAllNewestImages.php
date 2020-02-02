<?php
/**
 * This script will return the latest picture from each camera
 * Vers: 1.2.0
 */
include 'config.php';
include_once 'bifi.class.php';
include_once 'cameraTools.php';

header('Content-Type: application/json');

$images_array= array();
$filter="";

foreach (Constants::getCameras() as $camName=> $camera) {
    $path = Constants::IMAGE_ROOT_PATH.$camera["path"];
    $latest_ctime = 1;
    $latest_filename = '';
    $akttime="";
    if (isUserRoot() || isUserView() || $camera["webcam"]  ) {
        if ($camera["zip"]) {
            $directory = dir($path);
            while ($file = $directory->read()) {
                $akttime=intval(substr($file, 3,8));
                if (strtolower(substr($file, -4))===".bfi" &&  $akttime> $latest_ctime) {
                    $latest_ctime = $akttime;
                    $latest_filename = $file;
                }
            }

            //newest zip file found
            if ($latest_filename!="") {
                $zip = new BiFi();
                $zipFileName=substr($latest_filename,0,strlen($latest_filename)-4);
                $fzip=$path.$zipFileName;
                $zip->open($fzip);
                $item=$zip->statIndex($zip->numFiles-1);
                $zip->close();
                $ret=array();
                $ret["date"]=$latest_ctime.'';
                $ret["name"]=key($item);
                $images_array[$camName] =$ret;
            } else {
                $images_array[$camName] =array("name"=>null,"date"=>null);
            }
            $directory->close();

        } else {
            $path = Constants::IMAGE_ROOT_PATH.$camera["path"];
            $files = getFileList($path,$camera["patternRegEx"]);
            foreach ($files as $f) {
                if (($filter=="" || strstr($f["name"],$filter)) && intval($f["lastmod"]) > $latest_ctime) {
                    $latest_ctime = intval($f["lastmod"]);
                    $latest_filename = str_replace(Constants::IMAGE_ROOT_PATH.$camera["path"],'',$f["name"]);
                }
            }
            if ($latest_filename!="") {
                $date = (new DateTime)->setTimestamp($latest_ctime);
                $ret["date"] = $date->format("Ymd");
                $ret["name"] =$camera["path"].$latest_filename;
                $images_array[$camName] = $ret;
            }
        }
    } else {
        $ret["date"] = null;
        $ret["name"] = null;
        $images_array[$camName] = $ret;
    }


}
echo(json_encode($images_array));
?>