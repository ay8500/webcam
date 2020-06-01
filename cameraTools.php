<?php
/**
 * Camera tools
 * Vers. 1.2.0
 **/
include_once 'config.class.php';
include_once Config::$lpfw.'logger.class.php';
include_once 'phpmailer/PHPMailer.php';
include_once 'phpmailer/SMTP.php';
include_once 'phpmailer/Exception.php';

/**
 * Optain a list of file recursively from a directory using regex filter and delete empty directorys
 * @param $dir
 * @param $regex
 * @param int $maxFiles
 * @param int $level
 * @return array
 */
function getFileList($dir, $regex, $maxFiles = 3000, $level = 0)
{
    $ret = array();

    // add trailing slash if missing
    if (substr($dir, -1) != "/") {
        $dir .= "/";
    }

    // open pointer to directory and read list of files
    $d = @dir($dir);
    if (false !== $d) {
        while (FALSE !== ($entry = $d->read()) && ($maxFiles--) > 0) {
            // skip hidden files
            if ($entry{0} == ".") continue;
            if (is_dir("{$dir}{$entry}")) {
                $ret = array_merge($ret, getFileList($dir . $entry . "/", $regex, $maxFiles, ($level + 1)));
                error_reporting(E_ALL & ~E_WARNING);
                rmdir($dir . $entry);
            } elseif (is_readable($dir . $entry)) {
                if (preg_match("/" . $regex . "/", $dir . $entry)) {
                    $ret[count($ret)] = [
                        'name' => $dir . $entry,
                        //'type' => mime_content_type($dir.$entry),
                        //'size' => filesize($dir.$entry),
                        'lastmod' => filemtime($dir . $entry)
                    ];

                }
            }
        }
        $d->close();
    }
    return $ret;
}

/**
 * Delete images from one day
 * @param string $camType
 * @param string $camName
 * @param DateTime $day
 * @return string Delete message
 */
function deleteImagesFromDay($camType, $camName, $day)
{
    $fileDeletedCount = 0;
    $camera=Config::ja()["cameras"][$camName];
    $path = Config::jc()->IMAGE_ROOT_PATH . $camera["path"];
    if ($camera["zip"]) {
        $fileName = $path . "cam" . $day->format('Ymd') . ".zip";
        $fileDeletedCount += unlink($fileName . ".bfi")?1:0;
        $fileDeletedCount += unlink($fileName . ".bfd")?1:0;
    } else {
        $files = getFileList($path,$camera["patternRegEx"]);
        foreach ($files as $f){
            $d = (new DateTime())->setTimestamp($f["lastmod"] );
            if (($camType == "" || strstr($f["name"], $camType)) && $d->format("Ymd") === $day->format("Ymd")) {
                $fileDeletedCount += unlink($f["name"]) ? 1 : 0;
            }
        }
    }
    \maierlabs\lpfw\Logger::_("Delete day date:" . $day->format("Ymd") . " files:" . $fileDeletedCount, \maierlabs\lpfw\LoggerLevel::info);
    return  "Files deleted:" . $fileDeletedCount;
}

/**
 * Zip images in the archive file per day
 * Vers. 1.2.0
 * Only a defined number of images will be zipped at once! Call the funtion severel times to zip all images.
 * The count of max image are define in config.json Config::jc()->MAX_COUNT_TO_ZIP
 * @param string $camName
 * @param boolean $simulate if you oly want to simulate no archiving
 * @param boolean $delete in a file is successfully archived the oroginal file will be deleted
 */
function zipImages($camName,$delete=true,$simulate=true) {
    $fileZipped=0;$daysZipped=0;

    $camera=Config::ja()["cameras"][$camName];
    if ($camera["zip"]) {
        $path = Config::jc()->IMAGE_ROOT_PATH . $camera["path"];
        $files = getFileList($path,$camera["patternRegEx"],Config::jc()->MAX_COUNT_TO_ZIP);

        array_multisort(array_column($files,'lastmod'),SORT_ASC,$files);
        $deletefiles = array();
        $alertMail = array();
        $tobeZipped = count($files);

        if (count($files)>0) {
            $zip = new BiFi();
            $zipfilename = "";
            foreach ($files as $f) {
                $d=(new DateTime())->setTimestamp($f["lastmod"]);
                if (!$simulate) {
                    if ($zipfilename != $d->format('Ymd')) {
                        $zipfilename = $d->format('Ymd');
                        $zip->close();
                        if ($zip->open($path . "cam" . $zipfilename . ".zip", ZipArchive::CREATE)) {
                            $daysZipped++;
                        }
                    }
                    $resultOfZip=$zip->addFile( $f["name"], basename($f["name"]));
                } else {
                    $resultOfZip=true;
                }

                if ($resultOfZip) {
                    $fileZipped++;
                    //check if an alert file
                    if(isset($camera["alert"]) && isset($camera["alertEmail"]) && strpos($f["name"],$camera["alert"])!==false) {
                        array_push($alertMail,$f["name"]);
                    }
                    array_push($deletefiles, $f["name"]);
                }
            }
            $zip->close();
        }

        //delete them
        $deleted=0;
        if ($delete) {
            foreach ($deletefiles as $d) {
                if (!$simulate) {
                    if (unlink( $d))
                        $deleted++;
                } else {
                    $deleted++;
                }
            }
        }

        //make a nice return object
        $ret=new stdClass();
        $ret->tobeZipped=$tobeZipped;
        $ret->filesZipped=$fileZipped;
        $ret->daysZipped=$daysZipped;
        $ret->deleted=$deleted;
        $ret->sendMail=$alertMail;
    } else {
        //make a nice return object for camera not found
        $ret = new stdClass();
        $ret->tobeZipped = 0;
        $ret->filesZipped = 0;
        $ret->daysZipped = 0;
        $ret->deleted = 0;
        $ret->sendMail=array();
    }
    return $ret;

}


/**
 * Send a mail via smtp server
 * @param $to
 * @param $text
 * @return bool
 * @throws \PHPMailer\PHPMailer\Exception
 */
function sendSmtpMail ($to,$text,$tobcc=null) {
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    $mail->IsSMTP(); // telling the class to use SMTP
    $mail->CharSet = $mail::CHARSET_UTF8;
    $mail->ContentType = $mail::CONTENT_TYPE_TEXT_HTML;
    $mail->SMTPAuth = true; // enable SMTP authentication
    $mail->SMTPSecure = $mail::ENCRYPTION_STARTTLS; // sets the prefix to the servier
    $mail->Host = Config::jc()->EMAIL_HOST; // sets GMAIL as the SMTP server
    $mail->Port = Config::jc()->EMAIL_PORT; // set the SMTP port for the GMAIL server
    $mail->Username = Config::jc()->EMAIL_SENDER; // GMAIL username
    $mail->Password = Config::jc()->EMAIL_PASSWORD; // GMAIL password

    $mail->AddAddress($to);
    if ($tobcc!=null)
        $mail->addBCC($tobcc);
    $mail->SetFrom(Config::jc()->EMAIL_SENDER);
    $mail->Subject = Config::jc()->EMAIL_SUBJECT;
    $mail->msgHTML($text);

    try{
        $mail->Send();
        maierlabs\lpfw\Logger::_("Alert mail sent to:".$to,maierlabs\lpfw\LoggerLevel::info);
        return true;
    } catch(Exception $e){
        maierlabs\lpfw\Logger::_("Alert mail error:".$to." ".$mail->ErrorInfo,maierlabs\lpfw\LoggerLevel::error);
        return false;
    }
}