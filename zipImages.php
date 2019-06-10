<?php
include_once 'config.php';
include_once 'bifi.class.php';



/**
 * Zip images in a zip file per day
 * Only 100 images will be zipped at once! call the funtion sever times to zip all images
 * The count of max image are define in config.php
 * @param unknown $camName
 */

function zipImages($camName,$delete=true) {
    $fileZipped=0;$daysZipped=0;$tobeZipped=0;$deleted=0;$camPath=null;
    //Get the path of the camera
    foreach (Constants::getCameras() as $cn=>$propertys) {
        if ($cn==$camName && $propertys["zip"]) {
            $camPath=$propertys["path"];
        }
    }
    if ($camPath!==null) {
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
            //zip the files
            $tobeZipped = count($files);

            if (count($files)) {
                $zip = new BiFi();
                $zipfilename = "";
                foreach ($files as $f => $d) {
                    if ($zipfilename != date('Ymd', $d)) {
                        $zipfilename = date('Ymd', $d);
                        $zip->close();
                        $zipfilenamepath = $path . "cam" . $zipfilename . ".zip";
                        if ($zip->open($path . "cam" . $zipfilename . ".zip", ZipArchive::CREATE)) {
                            $daysZipped++;
                        }
                    }
                    if ($zip->addFile($path . $f, $f)) {
                        $fileZipped++;
                        array_push($deletefiles, $f);
                    }
                }
                $zip->close();
            }

            //delete them
            if ($delete) {
                foreach ($deletefiles as $d) {
                    if (unlink($path . $d))
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
    } else {
        //make a nice return object for camera not found
        $ret = new stdClass();
        $ret->tobeZipped = 0;
        $ret->filesZipped = 0;
        $ret->daysZipped = 0;
        $ret->deleted = 0;
    }
    return $ret;
}