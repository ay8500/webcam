<?php
class Constants
{
	const TITLE="Webcam by Levi";
	
	//The value for foscam should be "CameraName"=>"/directory/CameraType_xxxxxxxxxxxx/snap/"
	public static function IMAGE_PATH() {
		return array(	 "Kamera1"=>"FI9900P_00626E66039D/snap/"
						,"Kamera2"=>"FI9900P_C4D655408C9F/snap/"
						,"Thalmannsfeld"=>"FI9805W_00626E646465/snap/"
					);
	}
	
	const ZIPFILES=true;
	const MAX_COUNT_TO_ZIP=10;
	
	
	const IMAGE_ROOT_PATH="/var/www/usb/";
	
	public static function IMAGE_URL() {
		return "http://".$_SERVER["SERVER_NAME"]."/usb/";
	}
	
	//Display calendar minimal and maximal months refered to now. Example -3,2 will display 6 months
	const CALENDAR_MIN_DISPLAY=-1;
	const CALENDAR_MAX_DISPLAY=0;
	
	
	//Empty string for SNAP will not show snapshot pictures
	//For Foscam the value sould be: "Schedule_" 
	const SNAP="Schedule_";
	
	//Empty string for ALERT will not show alert pictures
	//For Foscam the value sould be: "MDAlarm_"
	const ALERT="MDAlarm_";
	
	const PASSW_VIEW="levi67";
	const PASSW_ROOT="camlevi67";
	
	//Delete pictures that are older than 
	const AUTO_DELETE_OLDER_THAN_DAYS=0;
	
	//Unse . (point) to delete all files, or MDAlarm_ to delete alarm files, or Schedule_ for scheduled filese  
	const AUTO_DELETE_FILTER=".";
}

?>