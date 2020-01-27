<?php
include_once 'config.php';
include_once 'config.class.php';
include_once Config::$lpfw.'logger.class.php';

/**
 * Delete images from one day
 * Vers. 1.2.0
 * @param string $camType
 * @param string $camName
 * @param DateTime $day
 * @return string Delete message
 */
function deleteImagesFromDay($camType, $camName, $day)
{
    $fileDeletedCount = 0;
    $camera=Constants::getCameras()[$camName];
    $path = Constants::IMAGE_ROOT_PATH . $camera["path"];
    if (Constants::getCameras()[$camName]["zip"]) {
        $fileName = $path . "cam" . $day->format('Ymd') . ".zip";
        $fileDeletedCount += unlink($fileName . ".bfi")?1:0;
        $fileDeletedCount += unlink($fileName . ".bfd")?1:0;
    } else {
        $files = getFileList($path,$camera["patternRegEx"]);
        foreach ($files as $f){
            $d = (new DateTime())->setTimestamp($f["lastmod"] );
            if (($camType == "" || strstr($f["name"], $camType)) && $d->format("Ymd") === $day->format("Ymd")) {
                $fileDeletedCount += unlink($f["name"])?1:0;
        }
    }
    \maierlabs\lpfw\Logger::_("Delete day date:" . $day->format("Ymd") . " files:" . $fileDeletedCount, \maierlabs\lpfw\LoggerLevel::info);
    return  "Files deleted:" . $fileDeletedCount;
}