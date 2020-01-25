<?php
include_once 'config.php';
include_once 'config.class.php';
include_once Config::$lpfw.'logger.class.php';

/**
 * Delete images from one day
 * @param string $camType
 * @param string $camName
 * @param DateTime $day
 * @return string Delete message
 */
function deleteImagesFromDay($camType, $camName, $day)
{
    $path = Constants::IMAGE_ROOT_PATH . Constants::getCameras()[$camName]["path"];
    $fileDeletedCount = 0;
    if (Constants::getCameras()[$camName]["zip"]) {
        $fileName = $path . "cam" . $day->format('Ymd') . ".zip";
        $fileDeletedCount += unlink($fileName . ".bfi")?1:0;
        $fileDeletedCount += unlink($fileName . ".bfd")?1:0;
    } else {
        $directory = dir($path);
        while ($file = $directory->read()) {
            if (in_array(strtolower(substr($file, -4)), array(".jpg", ".gif", ".png")) &&
                ($camType == "" || strstr($file, $camType)) && (new DateTime())->setTimestamp(filemtime($path . $file))->format("Ymd") === $day->format("Ymd")) {
                $fileDeletedCount += unlink($path . $file)?1:0;
            }
        }
        $directory->close();
    }
    \maierlabs\lpfw\Logger::_("Delete day date:" . $day->format("Ymd") . " files:" . $fileDeletedCount, \maierlabs\lpfw\LoggerLevel::info);
    return  "Files deleted:" . $fileDeletedCount;
}