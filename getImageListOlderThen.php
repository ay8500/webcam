<?php
/**
 * This script will delete older pictures than the day parameter from the archive or filesystem
 * Vers: 1.2.0
 */
include 'config.php';

header('Content-Type: application/json');

if (isset($_GET['action']))
	$action=$_GET['action'];
else
	$action="";

if (isset($_GET['day']) && $_GET['day']!="" )
	$day=new DateTime($_GET['day']);
else
	die("day parameter is missing");

if (isset($_GET['cam']) && $_GET['cam']!="" )
	$camName=$_GET['cam'];
else
	die("cam parameter is missing");

if (isset(Constants::getCameras()[$camName]))
	$propertys = Constants::getCameras()[$camName];
else
	die("camera propertys not set");

$count=0;
$path =Constants::IMAGE_ROOT_PATH.$propertys["path"];
$directory = dir($path);
while ($file = $directory->read()) {
	if($propertys["zip"]) {
		if (in_array(strtolower(substr($file, -4)), array(".bfd", ".bfi")) &&
			(new DateTime(substr($file,3,8)))->format("U")<intval($day->setTime(0,0)->format("U"))
			)
		{
			if ($action == "delete") {
				if (unlink($path . $file))
					$count++;
			} else {
				$count++;
			}
		}
	} else {
		//TODO recursive delete of files in subdirectories
		if (in_array(strtolower(substr($file, -4)), array(".jpg", ".gif", ".png"))&&
			filemtime($path . $file)<intval($day->format("U"))
		)
		{
			if ($action == "delete") {
				if (unlink($path . $file))
					$count++;
			} else {
				$count++;
			}
		}
	}
}
$directory->close();
$ret = array();
$ret["files"]=$count;
echo(json_encode($ret));
?>

