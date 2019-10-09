<?php
include_once 'config.php';
include_once 'bifi.class.php';



/**
 * Zip images in a zip file per day
 * Only a defined number of images will be zipped at once! Call the funtion severel times to zip all images.
 * The count of max image are define in config.php Constants::MAX_COUNT_TO_ZIP
 * @param string $camName
 */
function zipImages($camName,$delete=true,$simulate=true) {
    $fileZipped=0;$daysZipped=0;$tobeZipped=0;$deleted=0;$camPath=null;

    $propertys=Constants::getCameras()[$camName];
    $camPath=$propertys["path"];

    if ($camPath!==null && $propertys["zip"]) {
        //Server path
        $path = Constants::IMAGE_ROOT_PATH . $camPath;
        $directory = dir($path);
        $fileDeletedCount = 0;
        if ($directory !== false) {
            $count = Constants::MAX_COUNT_TO_ZIP;
            //Collect the files to zip
            $files = array();
            while (($file = $directory->read()) && $count > 0) {
                if (in_array(strtolower(substr($file, -4)), array(".jpg", ".gif", ".png"))) {
                    $files[$file] = filemtime($path . $file);
                    $count--;
                }
            }
            $directory->close();

            ksort($files);
            $deletefiles = array();
            $alertMail = array();
            //zip the files
            $tobeZipped = count($files);

            if (count($files)) {
                $zip = new BiFi();
                $zipfilename = "";
                foreach ($files as $f => $d) {
                    if (!$simulate) {
                        if ($zipfilename != date('Ymd', $d)) {
                            $zipfilename = date('Ymd', $d);
                            $zip->close();
                            $zipfilenamepath = $path . "cam" . $zipfilename . ".zip";
                            if ($zip->open($path . "cam" . $zipfilename . ".zip", ZipArchive::CREATE)) {
                                $daysZipped++;
                            }
                        }
                        $resultOfZip=$zip->addFile($path . $f, $f);
                    } else {
                        $resultOfZip=true;
                    }

                    if ($resultOfZip) {
                        $fileZipped++;
                        //check if an alert file
                        if(isset($propertys["alert"]) && isset($propertys["alertEmail"]) && strpos($f,$propertys["alert"])!==false) {
                            array_push($alertMail,$f);
                        }
                        array_push($deletefiles, $f);
                    }
                }
                $zip->close();
            }

            //delete them
            if ($delete) {
                foreach ($deletefiles as $d) {
                    if (!$simulate) {
                        if (unlink($path . $d))
                            $deleted++;
                    } else {
                        $deleted++;
                    }
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
