<?php
namespace maierlabs\phpunit;

include_once 'config.class.php';

class phpunit {

    /**
     * @param string $dir
     * @param array $excludeFiles
     * @return array
     */
    function getDirContents($dir, $excludeFiles=array()){
        $results = array();
        $files = scandir($dir);

        foreach($files as $key => $value){

            if(!is_dir($dir. DIRECTORY_SEPARATOR .$value)){
                if (strstr($value, 'Test.php') !== false) {
                    array_push($results,array("file"=>$value,"dir"=>$dir. DIRECTORY_SEPARATOR));
                }
            } else if(is_dir($dir. DIRECTORY_SEPARATOR .$value) && !in_array($value,$excludeFiles)) {
                $rr=$this->getDirContents($dir. DIRECTORY_SEPARATOR .$value,$excludeFiles);
                $results=array_merge($results, $rr);
            }
        }
        return $results;
    }

    function getTestClassMethods($className) {
        $ret = array();
        $methods=get_class_methods($className);
        if ($methods!=null) {
            foreach ($methods as $method) {
                if (!in_array(strtolower($method), array("setup", "teardown")) && strpos($method, "assert") !== 0) {
                    $ret[] = $method;
                }
            }
        }
        return $ret;
    }

    function getTestClassMethodsFromFile($fileName) {
        $ret = array();
        $methods = array();
        $lines = explode("\n",file_get_contents($fileName));
        foreach ($lines as $line) {
            $p1 = strpos($line,"public function test");
            if ($p1!==false) {
                $p2 = strpos($line,"()",$p1);
                if ($p2!==false) {
                    $p1 +=strlen("public function ");
                    $methods[]=substr($line,$p1,$p2-$p1);
                }
            }
        }
        if ($methods!=null) {
            foreach ($methods as $method) {
                if (!in_array(strtolower($method), array("setup", "teardown")) && strpos($method, "assert") !== 0) {
                    $ret[] = $method;
                }
            }
        }
        return $ret;
    }

    function getTestClassSetupMethod($className) {
        $methods=get_class_methods($className);
        foreach ($methods as $method) {
            if (strtolower($method)=="setup") {
                return $method;
            }
        }
        return null;
    }

    function getTestClassTearDownMethod($className) {
        $methods=get_class_methods($className);
        foreach ($methods as $method) {
            if (strtolower($method)=="teardown") {
                return $method;
            }
        }
        return null;
    }

    /**
     * Safety get paramateter read
     * @param string $name
     * @param string $def
     * @return string
     */
    function getGetParam($name,$def=null) {
        if (isset($_GET[$name]))
            return html_entity_decode(htmlentities($_GET[$name],ENT_QUOTES),ENT_NOQUOTES);
        return  $def;

    }


}