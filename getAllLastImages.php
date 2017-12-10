<?php
include 'config.php';
include_once 'bifi.class.php';

header('Content-Type: application/json');


$images_array= array();

$filter=".";

$idx=0;

foreach (Constants::IMAGE_PATH() as $camName=>$imgPath) {
	$path =Constants::IMAGE_ROOT_PATH.$imgPath;
	$directory = dir($path);
	$latest_ctime = 0;
	$latest_filename = '';
	if (isUserOk() ) {
		if (Constants::ZIPFILES) {
			while ($file = $directory->read()) {
				if (strtolower(substr($file, -4))==".bfi" && filectime($path.$file) > $latest_ctime) {
					$latest_ctime = filectime($path.$file);
					$latest_filename = $file;
				}
			}
			//newws zip file found 
			if ($latest_filename!="") {
				$zip = new BiFi();
				$fzip=$path.substr($latest_filename,0,strlen($latest_filename)-4);
				$zip->open($fzip);
				$item=$zip->statIndex($zip->numFiles-1);
				$zip->close();
				$item["date"]=substr($latest_filename,3,strlen($latest_filename)-11);
				$images_array[$camName] =$item;
			}
		} else {
			while ($file = $directory->read()) {
				if (in_array(strtolower(substr($file, -4)), array(".jpg",".gif",".png")) 
					&& strstr($file,$filter) && filectime(Constants::IMAGE_ROOT_PATH.$imgPath.$file) > $latest_ctime) {
		    		$latest_ctime = filectime(Constants::IMAGE_ROOT_PATH.$imgPath.$file);
		    		$latest_filename = $file;
				}
			}
			if ($latest_filename!="") 
				$images_array[$camName] = Constants::IMAGE_URL().$imgPath.$latest_filename;
		}
	} else {
		$images_array[$camName] ="password.jpg";
	}
	$directory->close();
}	


echo(json_encode($images_array));


function isUserOk() {
	return isset($_COOKIE["password"]) && ( $_COOKIE["password"]==Constants::PASSW_ROOT || $_COOKIE["password"]==Constants::PASSW_VIEW );
}
?>