<?php
class Constants
{
	const TITLE="Webcam by Levi";
    const EMAIL_SENDER="xxxxx@yyyyy.de";
    const EMAIL_PASSWORD="xxxxx";
    const EMAIL_HOST="smtp.yyyyy.de";
    const EMAIL_PORT="587";
    const EMAIL_SUBJECT="Webcam alert";

    public const CAMERAS='{
		"SV3C":{
			"path":"cam/cam1/",
			"patternRegEx":"[PA][0-9]{14}\\\\.jpg",
			"zip":true,
			"webcam":false,
			"snap":"P","alert":"A"
		},
    	"Kamera1":{
			"path":"cam/FI9900P_00626E66039D/",
			"patternRegEx":"(Schedule_)[0-9\\\\-]{15}\\\\.jpg|(MDAlarm_)[0-9\\\\-]{15}\\\\.jpg",
			"zip":true,
			"webcam":false,
			"snap":"Schedule_","alert":"MDAlarm_",
			"alertEmail":"code@blue-l.de"
		},
		"Kamera2":{
			"path":"cam/FI9900P_C4D655408C9F/",
			"patternRegEx":"(Schedule_)[0-9\\\\-]{15}\\\\.jpg|(MDAlarm_)[0-9\\\\-]{15}\\\\.jpg",
			"zip":true,
			"webcam":false,
			"snap":"Schedule_","alert":"MDAlarm_"
		},
		"Thalmannsfeld":{
			"path":"cam/FI9805W_00626E646465/",
			"patternRegEx":"(Schedule_)[0-9\\\\-]{15}\\\\.jpg|(MDAlarm_)[0-9\\\\-]{15}\\\\.jpg",
			"zip":true,
			"webcam":true,
			"snap":"Schedule_","alert":"MDAlarm_",
			"alertEmail":"code@blue-l.de"
		},
		"testflat":{
			"path":"test/jpg/",
			"patternRegEx":".*\\\\.jpg|.*\\\\.JPG",
			"zip":false,
			"webcam":true,
			"slides":true
		},
		"test":{
			"path":"test/pictures/",
			"patternRegEx":".*\\\\.jpg|.*\\\\.JPG",
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
	

	const PASSW_VIEW="e011d51843fe95fa05af3cdeb1ded601";
	const PASSW_ROOT="09358a859ea3f326558d72103edec2a8";
	
	//Delete pictures that are older than 
	const BATCH_DELETE_OLDER_THAN_DAYS=30000;
	
}

function isUserRoot() {
    return isset($_COOKIE["password"]) && ($_COOKIE["password"]==Constants::PASSW_ROOT || md5($_COOKIE["password"])==Constants::PASSW_ROOT);
}

function isUserView() {
    return isset($_COOKIE["password"]) && ($_COOKIE["password"]==Constants::PASSW_VIEW || md5($_COOKIE["password"])==Constants::PASSW_VIEW);
}



?>