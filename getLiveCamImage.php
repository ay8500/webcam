<?php
include 'config.php';
include_once 'bifi.class.php';


if (isset($_GET['camname']))
	$camname=$_GET['camname'];
else {
	echo("Parameter camname ist empty");
	header("HTTP/1.0 400 Bad Request");
die;
}

if (isset($_GET['delete']))
	$delete=true;
else 
	$delete=false;
	

$filter=".";

$imgPath=Constants::IMAGE_PATH()[$camname];

	$path =Constants::IMAGE_ROOT_PATH.$imgPath;
	$directory = dir($path);
	$latest_ctime = 0;
	$latest_filename = '';
	if(Constants::ZIPFILES) {
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
			$is = $zip->getFromName($item["name"]);
			$zip->close();unset($zip);
			
			$im = imagecreatefromstring($is);
			Header ("Content-type: image/jpg");
			ImageJpeg ($im);
			ImageDestroy ($im);
		}
	} else {
		while ($file = $directory->read()) {
			if (in_array(strtolower(substr($file, -4)), array(".jpg",".gif",".png")) && strstr($file,$filter) ) {
				if (filectime(Constants::IMAGE_ROOT_PATH.$imgPath.$file) > $latest_ctime) {
					$latest_ctime = filectime(Constants::IMAGE_ROOT_PATH.$imgPath.$file);
					$latest_filename = $file;
				} 
			}
		}
		if ($latest_filename!="") {
			$im = imagecreatefromjpeg(Constants::IMAGE_ROOT_PATH.$imgPath.$latest_filename);
			//if ($im) {
				Header ("Content-type: image/jpg");
				ImageJpeg ($im);
				ImageDestroy ($im);
			//}
		}
		$directory->close();
		
		if($delete) {
			$directory = dir($path);
			while ($file = $directory->read()) {
				if (in_array(strtolower(substr($file, -4)), array(".jpg",".gif",".png")) && strstr($file,$filter) ) {
					if (filectime(Constants::IMAGE_ROOT_PATH.$imgPath.$file) < $latest_ctime) {
						unlink(Constants::IMAGE_ROOT_PATH.$imgPath.$file);
					}
				}
			}
			$directory->close();
		}
	}
	
?>