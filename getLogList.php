<?php
include 'config.php';
include 'logger.class.php';

header('Content-Type: application/json');


if (isset($_GET['day']) && $_GET['day']!="" ) $day=new DateTime($_GET['day']); else $day=new DateTime();


$log= array();

if(isUserOk()) {
	$r=array();
	$f = fopen ("log", "r");
	$ln= 0;
	while ($line= fgets ($f)) {
		++$ln;
		$rr=explode("\t", $line,4);
		$time=substr($rr[0],0,10);
		$akttime=$day->format('Y-m-d');
		if($akttime==$time && $rr[2]==loggerLevel::info) {
			$r["date"]=$rr[0];
			$r["ip"]=$rr[1];
			$r["text"]=$rr[3];
			array_push($log, $r);
		}
	}
	fclose ($f);
	
}

echo(json_encode($log));

function isUserOk() {
	return isset($_COOKIE["password"]) && ( $_COOKIE["password"]==Constants::PASSW_ROOT || $_COOKIE["password"]==Constants::PASSW_VIEW );
}
?>