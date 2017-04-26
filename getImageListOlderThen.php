<?php
include 'config.php';

header('Content-Type: application/json');

if (isset($_GET['action']))
	$action=$_GET['action'];
else
	$action="";

/*
if (isset($_GET['days']) && intval($_GET['days'])>0)
	$days=intval($_GET['days']);
else
	die("You have to specify the days paramter als an integer bigger than 0");


if (isset($_GET['type']))
	$type=$_GET['type'];
else
	$type=".";

if (isset($_GET['day']) && $_GET['day']!="" )
	$day=new DateTime($_GET['day']);
else
	$day=new DateTime();
*/
$days=Constants::AUTO_DELETE_OLDER_THAN_DAYS;
$type=Constants::AUTO_DELETE_FILTER;
$day=new DateTime();

$images_array= array();

$filter=$type;

$idx=0;

foreach (Constants::IMAGE_PATH() as $camName=>$imgPath) {
	$path ="./".$imgPath;
	$directory = dir($path);	
	while ($file = $directory->read()) {
		if (in_array(strtolower(substr($file, -4)), array(".jpg",".gif",".png")) &&
		  strstr($file,$filter) &&
			($day->format("U") - filectime("./".$path.$file)) > 86400*$days	) {
			$images_array[$idx] = $path.$file;	
			$idx++;
			if ($action=="delete")
				unlink($path.$file);
		}
	}
	$directory->close();
}	
	
rsort($images_array);

echo(json_encode($images_array));
?>

