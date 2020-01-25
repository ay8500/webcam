<?php
//call this script without any parameters to move the camera images from all cameras in the archive files
//the main function is zipImages from the php file  zipImages.php
include_once 'config.php';
include_once 'zipImages.php';
include_once 'config.class.php';
include_once Config::$lpfw.'logger.class.php';

\maierlabs\lpfw\Logger::setLoggerType(\maierlabs\lpfw\LoggerType::file, Constants::IMAGE_ROOT_PATH.'zip.log');

foreach (Constants::getCameras() as $camName=>$propertys) {
    if($propertys["zip"]) {
        $ret = zipImages($camName,true,false);
        if (count($ret->sendMail)>0) {
            $mailsSent=sendAlertMail($propertys["alertEmail"],$camName,$ret->sendMail);
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
 * @param $pictureArra
 * @param $camname
 * @param $to
 * @param $from
 * @param $subject
 * @return boolean
 */
function sendAlertMail($to,$camName,$pictureArray) {
    $body = "<h2>".Constants::TITLE."</h2>";
    $body .="<p>Alert pictures from camera: ".$camName."</p>";
    $date = new DateTime();
    foreach ($pictureArray as $picture) {
        $body .='<img src="'.Constants::IMAGE_URL().'/getZipCamImage.php?camname='.$camName.'&imagename='.$picture.'&date='.$date->format("Ymd").'"/>';
    }
    $body  = "<body><html>".$body."</html></body>";

    $header[] = 'MIME-Version: 1.0';
    $header[] = 'Content-type: text/html; charset=utf-8';

    $header[] = 'To: '.$to;
    $header[] = 'From: Webcam<'.Constants::EMAIL_SENDER.'>';
    $header[] = 'Bcc: code@blue-l.de';

    return mail($to, Constants::EMAIL_ALERT_SUBJECT, $body, implode("\r\n", $header));
}
