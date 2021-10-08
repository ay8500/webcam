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
        $mailSentResult="";
        if (isset($camera["alertEmail"]) && $camera["alertEmail"]!="" && count($ret->sendMail)>0) {
            $toCC=(isset($camera["alertBccEmail"]) && $camera["alertBccEmail"]!="")?$camera["alertBccEmail"]:null;
            $mailsSent=sendAlertMail($camera["alertEmail"],$camName,$ret->sendMail,$toCC,true);
            $mailSentResult=$mailsSent?"ok":"error";
        }
        $text = $camName . '=>  to be Zipped:' . $ret->tobeZipped ;
        $text .= ' Zipped:' . $ret->filesZipped;
        $text .= ' Deleted:' . $ret->deleted;
        $text .= " Days:" . $ret->daysZipped;
        $text .= " Mail pictures:" . count($ret->sendMail);
        $text .= " Mails sent:" . $mailSentResult;
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
function sendAlertMail($to,$camName,$pictureArray,$tobcc=null,$onlyFirstAndLast) {
    $body = "<h2>".Config::jc()->TITLE."</h2>";
    $body .="<p>Alert pictures from camera: ".$camName."</p>";
    $date = new DateTime();
    foreach ($pictureArray as $id=>$picture) {
        if (!$onlyFirstAndLast || $id==0 || $id==sizeof($pictureArray)-1) {
            $body .= '<img src="' . Config::jc()->IMAGE_URL . '/getZipCamImage?camname=' . $camName . '&imagename=' . basename($picture) . '&date=' . $date->format("Ymd") . '"/>';
        }
    }
    if ($onlyFirstAndLast) {
        $body .= 'Number of taken pictures:' . sizeof($pictureArray);
    }
    $body  = "<body><html>".$body."</html></body>";

    return sendSmtpMail($to, $body, $tobcc);
}
