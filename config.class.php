<?php
include_once Config::$lpfw.'logger.class.php';
include_once Config::$lpfw.'ltools.php';

use maierlabs\lpfw\Logger as Logger;

//set logger output to file
Logger::setLoggerType(  \maierlabs\lpfw\LoggerType::file);
Logger::setLoggerLevel( \maierlabs\lpfw\LoggerLevel::info.\maierlabs\lpfw\LoggerLevel::error.\maierlabs\lpfw\LoggerLevel::debug);

class Config {

    public static $SiteTitle = "Webcam.";

    public static $siteUrl = "https://";
    public static $siteMail ="code@blue-l.de";
    public static $timeZoneOffsetMinutes=60;                // Server timezone eg: London=0, Berlin=60, Moscow=120
    public static $dateTimeFormat="<b>Y.m.d</b> H:i:s";
    public static $dateFormat="<b>Y.m.d</b>";

    public static $webAppVersion = "20200202";  //Used to load the actual css und js files.
    public static $SupportedLang = array("de"); //First language ist the default language.
    public static $lpfw = __DIR__. '/../lpfw/';

    /**
     * Get Database propertys host, database, user, password
     * @return object
     */
    public static function getDatabasePropertys()
    {
        $ret = new stdClass();
        if (!isLocalhost()) {
            $ret->host='';
            $ret->database='';
            $ret->user='';
            $ret->password='';
        } else {
            $ret->host='';
            $ret->database='';
            $ret->user='';
            $ret->password='';
        }
        return $ret;
    }
}
?>