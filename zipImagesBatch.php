<?php
include_once 'config.php';
include_once 'zipImages.php';
include_once 'logger.class.php';

setLoggerType(loggerType::file, Constants::IMAGE_ROOT_PATH.'zip.log');
foreach (Constants::IMAGE_PATH() as $camName=>$path) {
	$ret=zipImages($camName);
	$text=$camName.' Zipped:'.$ret->filesZipped.' Deleted:'.$ret->deleted." Days:".$ret->daysZipped;
	logger($text,loggerLevel::debug);
	echo($text."<br/>\n");
	
}
