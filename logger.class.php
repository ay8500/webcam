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
    $_SESSION['loggerLevel']=loggerLevel::debug.loggerLevel::info.loggerLevel::error;

//Default loggerType setzen
if (!isset($_SESSION['loggerType']))
    $_SESSION['loggerType']=loggerType::html;

//LoggerLevel aus der Sessiom
function setLoggerLevel($loggerLevel) {
    $_SESSION['loggerLevel']=$loggerLevel;
}

//LoggerType aus der Session
function setLoggerType($loggerType) {
    $_SESSION['loggerType']=$loggerType;
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
//Condition wird unver�ndert zur�ck gegeben
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
			//TODO
			logToFile("Array logger not impelented",$level);
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
			if (count($table)>0) {
				foreach($table as $qkey=>$arr) {
					$line="Line:".$qkey.";";
					foreach($arr as $key=>$value) {
						$line .=$key.":".$value.",";
					}
					logToFile($line,$level);
				}
			}
		}
	}
}

function logToFile($logText,$level){
	$file = './log';
	$text =date('Y-m-d H:i:s')."\t";
	$text .=$_SERVER["REMOTE_ADDR"]."\t";
	if (isset($_SESSION['USER']))
		$text .=$_SESSION['USER']."\t";
	$text .=$level."\t";
	$text .=$logText."\t";
	$text .= "\r\n";
	file_put_contents($file, $text, FILE_APPEND | LOCK_UN);
}


?>