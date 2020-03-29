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
        $ret=$this->callAjaxUrl($url."ajaxGetImageList.php",false);
        $this->assertNotNull($ret);
        $this->assertSame("Parameter camname ist empty",$ret->content );
        $this->assertSame(400,$ret->http_code);

        $ret=$this->callAjaxUrl($url."ajaxGetImageList.php?camname=test",true);
        $this->assertNotNull($ret);
        $this->assertSame(200,$ret->http_code);
        $this->assertCount(0,$ret->content);

        $ret=$this->callAjaxUrl($url."ajaxGetImageList.php?camname=test&day=2019-6-3&password=".md5(\Constants::PASSW_ROOT),true);
        $this->assertNotNull($ret);
        $this->assertCount(15,$ret->content);
        $this->assertSame("Schedule_20190603-033000.jpg",$ret->content[0] );

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
        if ($resp===false)
            return null;
        $ret = new stdClass();
        if ($json)
            $ret->content = json_decode($resp,true);
        else
            $ret->content = $resp;
        $ret->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $ret;
    }

    private function getUrl() {
        if( !is_array($_SERVER) || !isset($_SERVER["HTTP_REFERER"]))
            return null;
        $url=$_SERVER["HTTP_REFERER"];
        $url = substr($url,0,strlen($url)-strlen("phpunit/"));
        return $url."/webcam/";
    }
}