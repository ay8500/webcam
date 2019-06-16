<?php
class Constants
{
	const TITLE="Webcam by Levi";

    const CAMERAS='{
		"Kamera1":{
			"path":"cam/FI9900P_00626E66039D/snap/",
			"zip":true,
			"webcam":false,
			"snap":"Schedule_","alert":"MDAlarm_"
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
			"webcam":true
		},
		"testflat":{
			"path":"test/jpg/",
			"zip":false,
			"webcam":true
		},
		"test":{
			"path":"test/pictures/",
			"zip":true,
			"webcam":true
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
	const AUTO_DELETE_OLDER_THAN_DAYS=0;
	
	//Unse . (point) to delete all files, or MDAlarm_ to delete alarm files, or Schedule_ for scheduled filese  
	const AUTO_DELETE_FILTER=".";
}

?>