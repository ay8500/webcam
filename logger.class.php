<?php
if(!isset($_SESSION)) session_start();
 
class loggerLevel
{
    const info = "info";
    const debug = "debug";
    const error = "error";
}

class loggerType
{
	const none="";
	const html="html";
	const file="file";
	const db="db";
}

//Default loggerLevel setzen
if (!isset($_SESSION['loggerLevel']))
    setLoggerLevel(loggerLevel::debug.loggerLevel::info.loggerLevel::error);

//Default loggerType setzen
if (!isset($_SESSION['loggerType']))
    setLoggerType(loggerType::file);

//LoggerLevel aus der Sessiom
function setLoggerLevel($loggerLevel) {
    $_SESSION['loggerLevel']=$loggerLevel;
}

//LoggerType aus der Session
function setLoggerType($loggerType,$logFileName="log") {
    $_SESSION['loggerType']=$loggerType;
    $_SESSION['loggerFileName']=$logFileName;
}

//Text Loggen
function logger($text, $level=loggerLevel::info) {
	if (strrpos ($_SESSION['loggerLevel'],$level)>-1) {
		if ($_SESSION['loggerType']==loggerType::html) {
			echo('<span style="color:black;backgroundcolor:white;">'.$text."</span><br/>");
		}
		if ($_SESSION['loggerType']==loggerType::file) {
			logToFile($text,$level);
		}
	}
}

//Text Loggen wenn condition falsch, dann als Fehler
//Condition wird unverändert zurück gegeben
function loggerConditioned($condition, $text, $level=loggerLevel::debug) {
	if (strrpos ($_SESSION['loggerLevel'],$level)>-1) {
		if ($_SESSION['loggerType']==loggerType::html) {
			echo('<span style="color:black;backgroundcolor:white;">'.$text."</span><br/>");
		}
		if ($_SESSION['loggerType']==loggerType::file) {
			if ($condition)
				logToFile($text,$level);
			else
				logToFile($text,loggerLevel::error);
		}
	}
	return $condition;
}

//Array loggen
function loggerArray($arr, $level=loggerLevel::info) {
	if (strrpos ($_SESSION['loggerLevel'],$level)>-1) {
		if ($_SESSION['loggerType']==loggerType::html) {
		   echo("<table>");
		   foreach($arr as $key=>$value) {
			  echo("<tr>");
			  echo("<td>".$key."</td><td>".$value."</td>");
			  echo("</tr>");
		   }
		   echo("</table>");
		 }
		if ($_SESSION['loggerType']==loggerType::file) {
			foreach($arr as $key=>$value) {
				logToFile("Array:".$key."=".$value,$level);
		   	}
		}
	}
}

//Tabelle logen
function loggerTable($table, $level=loggerLevel::info) {
	if (strrpos ($_SESSION['loggerLevel'],$level)>-1) {
		if ($_SESSION['loggerType']==loggerType::html) {
			if (count($table)>0) {
				echo("<table>");
				echo("<tr>");
				foreach($table[0] as $key=>$value) {
					echo("<td>".$key."</td>");
				}
				echo("</tr>");
				foreach($table as $arr) {
					echo("<tr>");
					foreach($arr as $key=>$value) {
						echo("<td>".$value."</td>");
					}
					echo("</tr>");
				}
				echo("</table>");
			}
		}
		if ($_SESSION['loggerType']==loggerType::file) {
			logToFile(json_encode((object)$table),$level);
		}
	}
}

function logToFile($logText,$level){
	$text =date('Y-m-d H:i:s')."\t";
	$text .=$level."\t";
	$text .=(isset($_SERVER["REMOTE_ADDR"])?$_SERVER["REMOTE_ADDR"]:"")."\t";
	$text .=$_SERVER["SCRIPT_NAME"]."\t";
	if (isset($_SESSION['USER']))
		$text .=$_SESSION['USER']."\t";
	$text .=$logText."\t";
	$text .= "\r\n";
	if (isset($_SESSION['loggerFileName'])) {
        $logFileName=$_SESSION['loggerFileName'];
	} else {
        $logFileName='log';
	}
	if (file_exists($logFileName) && filesize($logFileName)>1024*2014*2) {
        $text = tail($logFileName).$text;
	    unlink($logFileName);
    }
    file_put_contents($logFileName, $text, FILE_APPEND | LOCK_UN);

}

function tail($filename, $lines = 2000, $buffer = 16384)
{
    $f = fopen($filename, "rb");

    // Jump to last character
    fseek($f, -1, SEEK_END);

    // Read it and adjust line number if necessary
    // (Otherwise the result would be wrong if file doesn't end with a blank line)
    if(fread($f, 1) != "\n") $lines -= 1;

    // Start reading
    $output = '';
    $chunk = '';

    // While we would like more
    while(ftell($f) > 0 && $lines >= 0)
    {
        // Figure out how far back we should jump
        $seek = min(ftell($f), $buffer);

        // Do the jump (backwards, relative to where we are)
        fseek($f, -$seek, SEEK_CUR);

        // Read a chunk and prepend it to our output
        $output = ($chunk = fread($f, $seek)).$output;

        // Jump back to where we started reading
        fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);

        // Decrease our line counter
        $lines -= substr_count($chunk, "\n");
    }

    // While we have too many lines
    // (Because of buffer size we might have read too many)
    while($lines++ < 0)
    {
        // Find first newline and remove all text before that
        $output = substr($output, strpos($output, "\n") + 1);
    }

    fclose($f);
    return $output;
}
?>