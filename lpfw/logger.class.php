<?php

namespace maierlabs\lpfw;

include_once "loggerType.class.php";
include_once  "loggerLevel.class.php";


/**
 * Class Logger
 * @package maierlabs\lpfw
 * @version 2019.05.10
 */
class Logger
{
    private static $maxFileSize = 500*1024;
    private static $loggerLevel=LoggerLevel::error;
    private static $loggerType=LoggerType::file;

    //LoggerLevel aus der Session
    public static function setLoggerLevel($loggerLevel)
    {
        self::$loggerLevel = $loggerLevel;
    }

    //LoggerType aus der Session
    public static function setLoggerType($loggerType)
    {
        self::$loggerType = $loggerType;
    }

    /**
     * log text
     * @param string $text
     * @param string $level
     */
    public static function _($text, $level = LoggerLevel::info)
    {
        if (strrpos(self::$loggerLevel, $level) > -1) {
            if (self::$loggerType == LoggerType::html) {
                echo('<span style="color:black;background-color:white;">' . $text . "</span><br/>");
            }
            if (self::$loggerType == LoggerType::file) {
                Logger::logToFile($text, $level);
            }
        }
    }

    //Text Loggen wenn condition falsch, dann als Fehler
    //Condition wird unverändert zurück gegeben
    public static function loggerConditioned($condition, $text, $level = LoggerLevel::info)
    {
        if (strrpos(self::$loggerLevel, $level) > -1) {
            if (self::$loggerType == LoggerType::html) {
                echo('<span style="color:black;background-color:white;">' . $text . "</span><br/>");
            }
            if (self::$loggerType == LoggerType::file) {
                if ($condition)
                    Logger::logToFile($text, $level);
                else
                    Logger::logToFile($text, LoggerLevel::error);
            }
        }
        return $condition;
    }

    //Array loggen
    public static function loggerArray($arr, $level = LoggerLevel::info)
    {
        if (strrpos(self::$loggerLevel, $level) > -1) {
            if (self::$loggerType == LoggerType::html) {
                echo("<table>");
                foreach ($arr as $key => $value) {
                    echo("<tr>");
                    echo("<td>" . $key . "</td><td>" . $value . "</td>");
                    echo("</tr>");
                }
                echo("</table>");
            }
            if (self::$loggerType == LoggerType::file) {
                Logger::logToFile("Array: " . var_export($arr, true), $level);
            }
        }
    }

    //Tabelle logen
    public static function loggerTable($table, $level = LoggerLevel::info)
    {
        if (strrpos(self::$loggerLevel, $level) > -1) {
            if (self::$loggerType == LoggerType::html) {
                if (count($table) > 0) {
                    echo("<table>");
                    echo("<tr>");
                    foreach ($table[0] as $key => $value) {
                        echo("<td>" . $key . "</td>");
                    }
                    echo("</tr>");
                    foreach ($table as $arr) {
                        echo("<tr>");
                        foreach ($arr as $key => $value) {
                            echo("<td>" . $value . "</td>");
                        }
                        echo("</tr>");
                    }
                    echo("</table>");
                }
            }
            if (self::$loggerType == LoggerType::file) {
                if (count($table) > 0) {
                    foreach ($table as $qkey => $arr) {
                        $line = "Line:" . $qkey . ";";
                        foreach ($arr as $key => $value) {
                            $line .= $key . ":" . $value . ",";
                        }
                        Logger::logToFile($line, $level);
                    }
                }
            }
        }
    }

    public static function logToFile($logText, $level)
    {
        $text = date('Y-m-d H:i:s') . "\t";
        $text .= $level . "\t";
        if (isset($_SERVER["REMOTE_ADDR"]) ) { //Need to be tested for PHPUnit
            $text .= $_SERVER["REMOTE_ADDR"] . "\t";
            $text .= $_SERVER["REQUEST_URI"] . "\t";
        }
        if (isset($_SESSION['uName']))
            $text .= $_SESSION['uName'] ;
        $text .= "\t".$logText . "\t";
        $text .= "\r\n";
        //Keep logfile above the maximum size
        if (file_exists(self::getLogfile()) && filesize(self::getLogfile())>self::$maxFileSize) {
            $text = self::tail(self::getLogfile()).$text;
            unlink(self::getLogfile());
        }
        file_put_contents(self::getLogfile(), $text, FILE_APPEND | LOCK_UN);
    }

    public static function readLogData($logText,$year, $length=100) {
        $logData = array();
        $logDataField = array("Date","Level","IP","URI","User","Text");
        $file=fopen(self::getLogfile(),"r");
        while (!feof($file)) {
            $b = explode("\t",fgets($file),6);
            if (sizeof($b)==6) {
                $c=explode("\t",$b[5],2);
                if (strpos($b[0],$year)!==false && strpos($logText,$c[0])!==false) {
                    $logEntry = array();
                    foreach($logDataField as $idx => $field) {
                        if (isset($b[$idx]))
                            $logEntry[$logDataField[$idx]] = $b[$idx];
                        else
                            $logEntry[$logDataField[$idx]] ="";
                    }
                    array_unshift($logData,$logEntry);
                }
            }
        }
        return array_slice($logData,0,$length);
    }

    private static function getLogfile() {
        $ret=dirname($_SERVER["SCRIPT_FILENAME"])."/log";
        return $ret;
    }

    private static function tail($filename, $lines = 1000, $buffer = 16384)
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
}