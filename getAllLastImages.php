<?php
include 'config.php';

header('Content-Type: application/json');


$images_array= array();

$filter=".";

$idx=0;

foreach (Constants::IMAGE_PATH as $camName=>$imgPath) {
	$path ="./".$imgPath;
	$directory = dir($path);
	$latest_ctime = 0;
	$latest_filename = '';
	if (isUserOk()) {
		while ($file = $directory->read()) {
			if (in_array(strtolower(substr($file, -4)), array(".jpg",".gif",".png")) &&
			  strstr($file,$filter) && filectime($path.$file) > $latest_ctime) {
	    		$latest_ctime = filectime($path.$file);
	    		$latest_filename = $path.$file;
			}
		}
		if ($latest_filename!="") 
			$images_array[$camName] = $latest_filename;
		$directory->close();
	} else {
		$images_array[$camName] ="password.jpg";
	}
}	


echo(json_encode($images_array));


function isUserOk() {
	return isset($_COOKIE["password"]) && ( $_COOKIE["password"]==Constants::PASSW_ROOT || $_COOKIE["password"]==Constants::PASSW_VIEW );
}
?>