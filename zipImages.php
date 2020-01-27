<?php
include_once 'config.php';
include_once 'bifi.class.php';
/**
 * Zip images in a zip file per day
 * Vers. 1.2.0
 * Only a defined number of images will be zipped at once! Call the funtion severel times to zip all images.
 * The count of max image are define in config.php Constants::MAX_COUNT_TO_ZIP
 * @param string $camName
 * @param boolean $simulate if you oly want to simulate no archiving
 * @param boolean $delete in a file is successfully archived the oroginal file will be deleted
 */
function zipImages($camName,$delete=true,$simulate=true) {
    $fileZipped=0;$daysZipped=0;

    $camera=Constants::getCameras()[$camName];

    if ($camera["zip"]) {
        $path = Constants::IMAGE_ROOT_PATH . $camera["path"];
        $files = getFileList($path,$camera["patternRegEx"],Constants::MAX_COUNT_TO_ZIP);

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
