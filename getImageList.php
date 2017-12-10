<?php
include 'config.php';
include_once 'bifi.class.php';

header('Content-Type: application/json');


if (isset($_GET['camname']))
	$camname=$_GET['camname'];
else {
	echo("Parameter camname ist empty");
	header("HTTP/1.0 400 Bad Request");
die;
}


if (isset($_GET['type']))
	$type=$_GET['type'];
else
	$type=Constants::SNAP;

if (isset($_GET['day']) && $_GET['day']!="" )
	$day=new DateTime($_GET['day']);
else
	$day=new DateTime();


$images_array= array();

if(isUserOk()) {
	$filter=$type;
	$ip=Constants::IMAGE_PATH();
	$path=Constants::IMAGE_ROOT_PATH.$ip[$camname];
	$idx=0;
	if (Constants::ZIPFILES) {
		$zip = new BiFi();
		$fzip=$path."cam".date_format($day, 'Ymd').".zip";
		if ($zip->open($fzip)) {
			for ($i=0; $i<$zip->numFiles;$i++) {
				if (strstr($zip->statIndex($i)['name'],$filter))  {
			    	$images_array[$idx++] = $zip->statIndex($i)['name'];
				}
			}	
		}
	} else {
		$directory = dir($path);	
		while ($file = $directory->read()) {
			if (in_array(strtolower(substr($file, -4)), array(".jpg",".gif",".png")) &&
			  strstr($file,$filter) ) {
				$images_array[$idx++] = Constants::IMAGE_URL().$ip[$camname].$file;	
			}
			
		}
		$directory->close();
	}
	rsort($images_array);
	echo(json_encode($images_array));
}
else {
	$images_array[0] = "./password.jpg";
}


function isUserOk() {
	return isset($_COOKIE["password"]) && ( $_COOKIE["password"]==Constants::PASSW_ROOT || $_COOKIE["password"]==Constants::PASSW_VIEW );
}
?>