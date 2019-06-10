<?php
include 'config.php';
include_once 'bifi.class.php';

header('Content-Type: application/json');


$images_array= array();

$filter="";

$idx=0;

foreach (Constants::getCameras() as $camName=>$propertys) {
    $path =Constants::IMAGE_ROOT_PATH.$propertys["path"];
    $directory = dir($path);
    $latest_ctime = 1;
    $latest_filename = '';
    if (isUserOk() && $directory!==false) {
        if ($propertys["zip"]) {

            while ($file = $directory->read()) {
                $akttime=intval(filemtime($path.$file));
                if (strtolower(substr($file, -4))===".bfi" &&  $akttime>= $latest_ctime) {
                    $latest_ctime = $akttime;
                    $latest_filename = $file;
                }
            }

            //newest zip file found
            if ($latest_filename!="") {
                $zip = new BiFi();
                $fzip=$path.substr($latest_filename,0,strlen($latest_filename)-4);
                $zip->open($fzip);
                $item=$zip->statIndex($zip->numFiles-1);
                $zip->close();
                $ret=array();
                $ret["date"]=substr($latest_filename,3,strlen($latest_filename)-11);
                $ret["name"]=key($item);
                $images_array[$camName] =$ret;
            }
        } else {
            while ($file = $directory->read()) {
                if (in_array(strtolower(substr($file, -4)), array(".jpg",".gif",".png"))
                    && ($filter=="" || strstr($file,$filter)) && intval(filemtime($path.$file)) > $latest_ctime) {
                    $latest_ctime = intval(filemtime($path.$file));
                    $latest_filename = $file;
                }
            }
            if ($latest_filename!="") {
                $date = (new DateTime)->setTimestamp($latest_ctime);
                $ret["date"] = $date->format("Ymd");
                $ret["name"] =$propertys["path"].$latest_filename;
                $images_array[$camName] = $ret;
            }
        }
        $directory->close();
    } else {
        $images_array[$camName]=array();
    }


}


echo(json_encode($images_array));


function isUserOk() {
    return isset($_COOKIE["password"]) && ( $_COOKIE["password"]==Constants::PASSW_ROOT || $_COOKIE["password"]==Constants::PASSW_VIEW );
}
?>