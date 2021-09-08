<?php
/**
 * call this script to post pictures in one camera big file
 * post parameter camera the camera name in config file
 * post parameter  file_xxx the picture file
 */
include_once 'config.class.php';
include_once 'cameraTools.php';
include_once 'bifi.class.php';

//include_once Config::$lpfw.'logger.class.php';
//\maierlabs\lpfw\Logger::setLoggerType(\maierlabs\lpfw\LoggerType::file, Config::jc()->IMAGE_ROOT_PATH.'post.log');

$cameraName = getParam("camera");
if ($cameraName==null) {
    die ("Fatal error: paramter camera missing!");
}
$cameras = Config::ja()["cameras"];
if (!isset($cameras[$cameraName])) {
    die ("Fatal error: camera not found");
}
$camera = $cameras[$cameraName];
$path = Config::jc()->IMAGE_ROOT_PATH . $camera["path"];
$zipfilename = (new DateTime())->format('Ymd');
$zip = new BiFi();
if (!$zip->open($path . "cam" . $zipfilename . ".zip", ZipArchive::CREATE)) {
    die ("Fatal error: big file opening failure");
}
echo("camera:".$cameraName." zipped files:".$zip->numFiles);
$files = 0;
$errorFiles = 0;
foreach ($_FILES as $paramName => $file) {
    if (strpos($paramName,"file_")===0) {
        if ($zip->addFile( $file["tmp_name"], $file["name"])) {
            $files++;
        } else {
            $errorFiles++;
        }
        echo("<br/>\r\nfile:".$file["name"]." size:".$file["size"]);
    }
}
echo("<br/>\r\ninserted:".$files." error files:".$errorFiles." zipped files:".$zip->numFiles);

$zip->close();

