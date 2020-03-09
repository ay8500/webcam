<?php
/**
 * This script returns the list of log entry in json format for a specified day.
 * Only works with root rights
 * Vers. 1.2.0
 **/
include_once 'config.php';
include_once 'config.class.php';
include_once Config::$lpfw.'logger.class.php';

header('Content-Type: application/json');

if (isset($_GET['day']) && $_GET['day']!="" ) $day=new DateTime($_GET['day']); else $day=new DateTime();

$log= array();

if(isUserRoot()) {
	$r=array();
	$f = fopen (Constants::IMAGE_ROOT_PATH.'log', "r");
	$ln= 0;
	while ($line= fgets ($f)) {
		++$ln;
		$rr=explode("\t", $line,5);
		$time=substr($rr[0],0,10);
		$akttime=$day->format('Y-m-d');
		if($akttime==$time && $rr[1]==maierlabs\lpfw\LoggerLevel::info) {
			$r["date"]=$rr[0];
			$r["ip"]=$rr[2];
			$r["text"]=$rr[4];
			array_push($log, $r);
		}
	}
	fclose ($f);
}
echo(json_encode($log));
