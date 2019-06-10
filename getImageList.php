<?php
include 'config.php';
include_once 'bifi.class.php';

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

if(!isUserOk()) {
    $images_array= array();
    $images_array[0] = "./password.jpg";
    echo(json_encode($images_array));
    die();
} else {
    $filter=$type;
    if (!isset(Constants::getCameras()[$camname])) {
        $images_array= array();
        echo(json_encode($images_array));
        die();
    }
    $path=Constants::IMAGE_ROOT_PATH.Constants::getCameras()[$camname]["path"];
    if (Constants::getCameras()[$camname]["zip"]) {
        $firstEntry=true;
        echo('[');
        $zip = new BiFi();
        $fzip=$path."cam".date_format($day, 'Ymd').".zip";
        if ($zip->open($fzip)) {
            for ($i=0; $i<$zip->numFiles;$i++) {
                $name=key($zip->statIndex($i));
                if($name!="") {
                    if ($filter=="" || strstr($name,$filter))  {
                        if($firstEntry) {
                            $firstEntry=false;
                        } else {
                            echo(",");
                        }
                        echo('"'.$name.'"');
                    }
                }
            }
        }
        echo(']');
    } else {
        $images_array= array();
        $idx=0;
        $directory = dir($path);
        while ($file = $directory->read()) {
            if (in_array(strtolower(substr($file, -4)), array(".jpg",".gif",".png")) &&
                ($filter=="" || strstr($file,$filter)) &&
                $day->format("Ymd")==(new DateTime())->setTimestamp(filemtime(Constants::getCameras()[$camname]["path"].$file))->format("Ymd") )
            {
                $images_array[$idx++] = Constants::getCameras()[$camname]["path"].$file;
            }

        }
        $directory->close();
        rsort($images_array);
        echo(json_encode($images_array));
    }
}

function isUserOk() {
    if (isset($_GET["password"])){
        return $_GET["password"]===md5(Constants::PASSW_ROOT);
    }
    return isset($_COOKIE["password"]) && ( $_COOKIE["password"]==Constants::PASSW_ROOT || $_COOKIE["password"]==Constants::PASSW_VIEW );
}
?>