<?php

include_once __DIR__ . "/../lpfw/logger.class.php";
include_once __DIR__ . "/../config.php";
include_once __DIR__ . "/../bifi.class.php";

use \maierlabs\lpfw\MySqlDbAUH as MySqlDbAUH;

class BigFileTest extends \PHPUnit_Framework_TestCase
{

    const TEST_PATH = __DIR__ ."/pictures/";
    const TEST_FILE = "index_test.zip";

    public function setup()
    {
        \maierlabs\lpfw\Logger::setLoggerLevel(\maierlabs\lpfw\LoggerLevel::info);
    }


    public function testOpenBigfile()
    {
        $path = Constants::IMAGE_ROOT_PATH . Constants::getCameras()["test"]["path"];
        $day = new DateTime("2019-6-3");
        $zip = new BiFi();
        $fzip = $path ."cam". date_format($day, 'Ymd') . ".zip";
        if ($zip->open($fzip)) {

            $this->assertSame(15, $zip->numFiles);

            $ret = $zip->getFileInfo("index", 2);
            $this->assertSame("Schedule_20190603-035000.jpg", key($ret));

            $ret = $zip->getFromName("Schedule_20190603-035000.jpg");
            $this->assertSame(129549, strlen($ret));


            $zip->close();
        } else {
            $this->assertTrue(false, "Failed to open the bigfile.");
        }
    }

    public function testAddFileToIndexFile()
    {
        $fzip = self::TEST_PATH. self::TEST_FILE;
        $zip = new BiFi();

        //Delete testfile and add to entrys
        if ($zip->open($fzip)) {
            $this->assertTrue($zip->deleteAchive());
            $ret = array();
            $ret["d"] = 123123;
            $ret["l"] = 12345;
            $ret["p"] = 0;
            $item = array();
            $item["test_file_1"] = $ret;
            $this->assertTrue($zip->addIndex($item));
            $ret["d"] = 321321;
            $ret["l"] = 23456;
            $ret["p"] = 12345;
            $item = array();
            $item["test_file_2"] = $ret;
            $this->assertTrue($zip->addIndex($item));
            $zip->close();
        } else {
            $this->assertTrue(false, "Failed to open new index file.");
        }

        //check the two entrys
        if ($zip->open($fzip)) {
            $this->assertSame(2, $zip->numFiles);
            $this->assertCount(1, $zip->statIndex(0));
            $this->assertCount(1, $zip->statIndex(1));
            $this->assertFalse($zip->statIndex(2));
        } else {
            $this->assertTrue(false, "Failed to open new index file.");
        }

        //delete one entry
        if ($zip->open($fzip)) {
            $this->assertFalse($zip->deleteName("no_entry_with_this_name"));
            $this->assertTrue($zip->deleteName("test_file_2"));
            $this->assertSame(1, $zip->numFiles);
        } else {
            $this->assertTrue(false, "Failed to open new index file.");
        }
        if ($zip->open($fzip)) {
            $this->assertSame(1, $zip->numFiles);
            $this->assertTrue($zip->deleteAchive());
        } else {
            $this->assertTrue(false, "Failed to open new index file.");
        }

    }

    public function testAddFileToArchive()
    {
        $fzip = self::TEST_PATH. self::TEST_FILE;
        $zip = new BiFi();

        for($l=1;$l<=13;$l++) {
            $this->assertTrue($this->createTestFile(self::TEST_PATH,$l));
        }

        //Delete test file and add to entrys
        $zip->open($fzip);
        $this->assertTrue($zip->deleteAchive());
        $this->assertFalse($zip->addFile("no_such_file","no_name"));
        for($nr=1;$nr<=13;$nr++) {
            $fileName = self::TEST_PATH . "testfile" . $nr . ".txt";
            $this->assertTrue($zip->addFile( $fileName, "file_".$nr));
        }
        $zip->close();

        //Delete temp files
        for($l=1;$l<=13;$l++) {
            $this->assertTrue($this->delteTestFile(self::TEST_PATH,$l));
        }

        //Check entrys content
        $zip->open($fzip);
        $this->assertSame(13,$zip->numFiles);
        for($nr=1;$nr<=13;$nr++) {
            $idx=$zip->statIndex($nr-1);
            $name = "file_".$nr;
            $this->assertSame($name,key($idx));
            $this->assertSame($this->createTestTxt($nr),$zip->getFromName( $name));
        }
        $zip->close();

        //Delete two entrys 2 an 5
        $zip->open($fzip);
        $this->assertSame(13,$zip->numFiles);
        $this->assertTrue($zip->deleteName( "file_2"));
        $this->assertTrue($zip->deleteName( "file_5"));

        $zip->open($fzip);
        $this->assertSame(11,$zip->numFiles);
        $ret = $zip->reorganize(true);
        $this->assertLessThan($ret['oldSize'],$ret['calcSize']);

        $zip->open($fzip);
        $ret = $zip->reorganize(false);
        $this->assertLessThan($ret['oldSize'],$ret['newSize']);
        $this->assertSame($ret['calcSize'],$ret['newSize']);
        print_r($ret);


        $zip->open($fzip);
        $ret = $zip->reorganize(false);
        $this->assertLessThan($ret['oldSize'],$ret['newSize']);
        $this->assertSame(0,$ret['freeSize']);
        print_r($ret);

        //Check entrys content
        $zip->open($fzip);
        for($i=0;$i<$zip->numFiles;$i++) {
            $idx=$zip->statIndex($i);
            $name = key($idx);
            $nr = intval(explode("_",$name)[1]);
            $text = $this->createTestTxt($nr);
            $content = $zip->getFromName( $name);
            $this->assertSame($text,$content);
        }
        $zip->close();

        //Delete test file
        $this->assertTrue($zip->deleteAchive());
        $zip->close();

    }


//***************************************[ Private ]*******************************

    private function createTestFile($path, $nr) {
        $fileName=self::TEST_PATH."testfile".$nr.".txt";
        $fp=fopen($fileName,"w");
        if ($fp===false)
            return false;

        if (!fwrite($fp,$this->createTestTxt($nr))) {
            return false;
        }
        fclose($fp);
        return true;
    }

    private function createTestTxt($nr) {
        $ret = "content_file_nr_".$nr."\r\n"."Line_2_file_nr_".$nr;

        for ($i=0;$i<intval(abs(sin($nr*6.1091827432))*10000);$i++) {
            $ret .=chr(32+intval(abs(sin($i*$nr*5.0123)*100)) % 64);
        }

        return $ret;
    }

    private function delteTestFile($path, $nr) {
        $fileName = self::TEST_PATH . "testfile" . $nr . ".txt";
        return unlink($fileName);
    }

}