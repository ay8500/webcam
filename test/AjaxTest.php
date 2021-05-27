<?php

include_once __DIR__ . "/../../webcam/config.class.php";
include_once __DIR__ . "/../../lpfw/logger.class.php";

class AjaxTest extends \PHPUnit_Framework_TestCase
{

    public function setup()
    {
        \maierlabs\lpfw\Logger::setLoggerLevel(\maierlabs\lpfw\LoggerLevel::info);
    }

    public function testGetImageList() {
        $url="/webcam/";
        $ret=$this->callTestUrl($url."ajaxGetImageList",false);
        $this->assertNotNull($ret);
        $this->assertSame("Parameter camname ist empty",$ret->content );
        $this->assertSame(400,$ret->http_code);

        $ret=$this->callTestUrl($url."ajaxGetImageList?camname=test",true);
        $this->assertNotNull($ret);
        $this->assertSame(200,$ret->http_code);
        $this->assertCount(0,$ret->content);

        $ret=$this->callTestUrl($url."ajaxGetImageList?camname=test&day=2019-6-3&password=".(\Config::jc()->user->root->password),true);
        $this->assertNotNull($ret);
        $this->assertCount(15,$ret->content);
        $this->assertSame("Schedule_20190603-033000.jpg",$ret->content[0]);

    }


    private function getUrl() {
        if( !is_array($_SERVER) || !isset($_SERVER["HTTP_REFERER"]))
            return null;
        $url=pathinfo($_SERVER["HTTP_REFERER"], PATHINFO_DIRNAME);
        $url = substr($url,0,strlen($url)-strlen("phpunit/"));
        return $url."/webcam/";
    }

}