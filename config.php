<?php
class Constants
{
	const TITLE="Webcam: Localhost";
	
	//The value for foscam should be "CameraName"->"CameraType_xxxxxxxxxxxx/snap/"
						
	public function IMAGE_PATH() {
		return array( "Kamera1"=>"images/snap1/"
					,"Kamera2"=>"images/snap2/"
		);
	}
						
	
	//Display calendar minimal and maximal months refered to now. Example -3,2 will display 6 months
	const CALENDAR_MIN_DISPLAY=-3;
	const CALENDAR_MAX_DISPLAY=0;
	
	
	//Empty string for SNAP will not show snapshot pictures
	//For Foscam the value sould be: "Schedule_" 
	const SNAP="Schedule_";
	
	//Empty string for ALERT will not show alert pictures
	//For Foscam the value sould be: "MDAlarm_"
	const ALERT="MDAlarm_";
	
	const PASSW_VIEW="slawitsch";
	const PASSW_ROOT="levi67";
	
	//Delete pictures that are older than 
	const AUTO_DELETE_OLDER_THAN_DAYS=30;
	
	//Unse . (point) to delete all files, or MDAlarm_ to delete alarm files, or Schedule_ for scheduled filese  
	const AUTO_DELETE_FILTER=".";
}

?>