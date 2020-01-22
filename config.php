<?php
class Constants
{
	const TITLE="Webcam by Levi";
    const EMAIL_SENDER="code@blue-l.de";
    const EMAIL_ALERT_SUBJECT="Webcam alert";

    const CAMERAS='{
		"Kamera1":{
			"path":"cam/FI9900P_00626E66039D/snap/",
			"zip":true,
			"webcam":false,
			"snap":"Schedule_","alert":"MDAlarm_",
			"alertEmail":"code@blue-l.de"
		},
		"Kamera2":{
			"path":"cam/FI9900P_C4D655408C9F/snap/",
			"zip":true,
			"webcam":false,
			"snap":"Schedule_","alert":"MDAlarm_"
		},
		"Thalmannsfeld":{
			"path":"cam/FI9805W_00626E646465/snap/",
			"zip":true,
			"webcam":true,
			"snap":"Schedule_","alert":"MDAlarm_",
			"alertEmail":"code@blue-l.de"
		},
		"testflat":{
			"path":"test/jpg/",
			"zip":false,
			"webcam":true
		},
		"test":{
			"path":"test/pictures/",
			"zip":true,
			"webcam":true,
			"alertEmail":"code@blue-l.de"
		}
	}';

    public static function getCameras() {
        return json_decode(self::CAMERAS,true);
    }

    const MAX_COUNT_TO_ZIP=10;

	const IMAGE_ROOT_PATH="c:\\xampp\\htdocs\\webcam\\";
	
	public static function IMAGE_URL() {
		return "http://".$_SERVER["SERVER_NAME"]."/webcam";
	}
	
	//Display calendar minimal and maximal months refered to now. Example -3,2 will display 6 months
	const CALENDAR_MIN_DISPLAY=-1;
	const CALENDAR_MAX_DISPLAY=0;
	

	const PASSW_VIEW="levi67";
	const PASSW_ROOT="camlevi67";
	
	//Delete pictures that are older than 
	const BATCH_DELETE_OLDER_THAN_DAYS=30000;
	
	//Unse . (point) to delete all files, or MDAlarm_ to delete alarm files, or Schedule_ for scheduled files
	const AUTO_DELETE_FILTER=".";
}

function isUserRoot() {
    return isset($_COOKIE["password"]) && $_COOKIE["password"]==Constants::PASSW_ROOT;
}

function isUserView() {
    return isset($_COOKIE["password"]) && $_COOKIE["password"]==Constants::PASSW_VIEW;
}

?>