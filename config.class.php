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
    public static $json =null;

    /**
     * Get Database propertys host, database, user, password
     * @return object
     */
    public static function getDatabasePropertys()
    {
        $ret = new stdClass();
        $ret->host='';
        $ret->database='';
        $ret->user='';
        $ret->password='';
        return $ret;
    }

    static function saveConfigJson($cameras,$newPassword) {
        $json=json_decode(file_get_contents("config.json"));
        if($newPassword!=null) {
            if (self::isUserView()){
                $json->user->view->password=$newPassword;
                $json->user->view->changeDate=date("Y.m.d H:i:s");
                $json->user->view->changeIP = $_SERVER["REMOTE_ADDR"];
            }
            if (self::isUserRoot()){
                $json->user->root->password=$newPassword;
                $json->user->root->changeDate=date("Y.m.d H:i:s");
                $json->user->root->changeIP = $_SERVER["REMOTE_ADDR"];
            }
        }
        foreach ($cameras as $camName => $camera) {
            if ($camName!=$camera->name) {
                $json->cameras->{$camera->name} = $json->cameras->{$camName};
                unset($json->cameras->{$camName});
            }
            $json->cameras->{$camera->name}->alertEmail = $camera->alertEmail;
            if (isset($camera->alertBccEmail))
                $json->cameras->{$camera->name}->alertBccEmail = $camera->alertBccEmail;
            $json->cameras->{$camera->name}->webcam = $camera->webcam;
        }
        $json->changeDate=date("Y.m.d H:i:s");
        $json->changeIP = $_SERVER["REMOTE_ADDR"];
        $json = json_encode($json,JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES);
        return file_put_contents("config.json", $json);
    }


    static function loadConfigJson() {
        if (self::$json==null) {
            self::$json=file_get_contents("config.json");
            $j = json_decode(self::$json);
            if (is_object($j) && isset($j->IMAGE_URL))
                $j->IMAGE_URL = "http://" . $_SERVER["SERVER_NAME"] . $j->IMAGE_URL;
            else
                die ("Error in config.json");
            self::$json = json_encode($j);
        }
    }

    public static function jc() {
        self::loadConfigJson();
        return json_decode(self::$json);
    }

    public static function ja() {
        self::loadConfigJson();
        return json_decode(self::$json,true);
    }

    public static function isUserRoot() {
        return isset($_COOKIE["password"]) && ($_COOKIE["password"]==self::jc()->user->root->password || md5($_COOKIE["password"])==self::jc()->user->root->password);
    }

    public static function isUserView() {
        return isset($_COOKIE["password"]) && ($_COOKIE["password"]==self::jc()->user->view->password || md5($_COOKIE["password"])==self::jc()->user->view->password);
    }

}
?>