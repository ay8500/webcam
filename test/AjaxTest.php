<?php

include_once __DIR__ . "/../../webcam/config.php";
include_once __DIR__ . "/../../lpfw/logger.class.php";

class AjaxTest extends \PHPUnit_Framework_TestCase
{

    public function setup()
    {
        \maierlabs\lpfw\Logger::setLoggerLevel(\maierlabs\lpfw\LoggerLevel::info);
    }

    public function testGetImageList() {
        $url=$this->getUrl();
        if($url==null)
            return;
        $ret=$this->callAjaxUrl($url."getImageList.php",false);
        $this->assertNotNull($ret);
        $this->assertSame("Parameter camname ist empty",$ret );

        $ret=$this->callAjaxUrl($url."getImageList.php?camname=test",true);
        $this->assertNotNull($ret);
        $this->assertCount(1,$ret);
        $this->assertSame("./password.jpg",$ret[0] );

        $ret=$this->callAjaxUrl($url."getImageList.php?camname=test&day=2019-6-3&password=".md5(\Constants::PASSW_ROOT),true);
        $this->assertNotNull($ret);
        $this->assertCount(15,$ret);
        $this->assertSame("Schedule_20190603-033000.jpg",$ret[0] );

    }


    private function callAjaxUrl($url,$json=true){
        if ($url==null || strlen($url)==0)
            return null;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
        //curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
        $resp = curl_exec($ch);
        curl_close($ch);
        if ($resp===false)
            return null;
        if ($json)
            return json_decode($resp,true);
        return $resp;
    }

    private function getUrl() {
        if( !is_array($_SERVER) || !isset($_SERVER["HTTP_REFERER"]))
            return null;
        $url=$_SERVER["HTTP_REFERER"];
        $url = substr($url,0,strlen($url)-strlen("phpunit/"));
        return $url."/webcam/";
    }
}