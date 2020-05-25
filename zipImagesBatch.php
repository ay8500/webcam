<?php
/**
* call this script without any parameters to move the camera images from all cameras in the archive files
* the main function is zipImages from the php file  comeraTools.php
* Version: 1.2.0
 */
include_once 'config.class.php';
include_once 'cameraTools.php';
include_once 'bifi.class.php';
include_once Config::$lpfw.'logger.class.php';

\maierlabs\lpfw\Logger::setLoggerType(\maierlabs\lpfw\LoggerType::file, Config::jc()->IMAGE_ROOT_PATH.'zip.log');

foreach (Config::ja()["cameras"] as $camName=> $camera) {
    if($camera["zip"]) {
        $ret = zipImages($camName,true,false);
        if (isset($camera["alertEmail"]) && count($ret->sendMail)>0) {
            $mailsSent=sendAlertMail($camera["alertEmail"],$camName,$ret->sendMail);
        } else
            $mailsSent=true;
        $text = $camName . '=>  to be Zipped:' . $ret->tobeZipped ;
        $text .= ' Zipped:' . $ret->filesZipped;
        $text .= ' Deleted:' . $ret->deleted;
        $text .= " Days:" . $ret->daysZipped;
        $text .= " Mail pictures:" . count($ret->sendMail);
        $text .= " Mails sent:" . ($mailsSent?"ok":"error");
        \maierlabs\lpfw\Logger::_($text, \maierlabs\lpfw\LoggerLevel::debug);
        echo($text . "<br/>\n");
    }
}

/**
 * Send alert mail
 * @param $pictureArray
 * @param $camname
 * @param $to
 * @return boolean
 */
function sendAlertMail($to,$camName,$pictureArray) {
    $body = "<h2>".Config::jc()->TITLE."</h2>";
    $body .="<p>Alert pictures from camera: ".$camName."</p>";
    $date = new DateTime();
    foreach ($pictureArray as $picture) {
        $body .='<img src="'.Config::jc()->IMAGE_URL.'/getZipCamImage?camname='.$camName.'&imagename='.basename($picture).'&date='.$date->format("Ymd").'"/>';
    }
    $body  = "<body><html>".$body."</html></body>";

    return sendSmtpMail($to, $body);
}
