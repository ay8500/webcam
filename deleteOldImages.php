<?php
include_once 'config.php';
include_once 'config.class.php';
include_once Config::$lpfw.'logger.class.php';

header('Content-Type: application/json');

if (isset($_GET['action']))
    $action=$_GET['action'];
else
    $action="";
\maierlabs\lpfw\Logger::setLoggerType(\maierlabs\lpfw\LoggerType::file, Constants::IMAGE_ROOT_PATH.'delete.log');
$day=new DateTime();
$day->modify('-'.Constants::BATCH_DELETE_OLDER_THAN_DAYS.' day');

$ret = array();
$ret["action"]=$action;

foreach (Constants::getCameras() as $camName=>$propertys) {
    $count=0;
    $path =Constants::IMAGE_ROOT_PATH.$propertys["path"];
    $directory = dir($path);
    while ($file = $directory->read()) {
        if($propertys["zip"]) {
            try {
                if (in_array(strtolower(substr($file, -4)), array(".bfd", ".bfi")) &&
                    (new DateTime(substr($file, 3, 8)))->format("U") < intval($day->setTime(0, 0)->format("U"))
                ) {
                    if ($action == "delete") {
                        if (unlink($path . $file))
                            $count++;
                    } else {
                        $count++;
                    }
                }
            } catch (Exception $e) {
                ;//an error can occur if the filename doesn't have the right form e.g. while phpunit tests
            }
        } else {
            if (in_array(strtolower(substr($file, -4)), array(".jpg", ".gif", ".png"))&&
                filemtime($path . $file)<intval($day->format("U"))
            )
            {
                if ($action == "delete") {
                    if (unlink($path . $file))
                        $count++;
                } else {
                    $count++;
                }
            }
        }
    }
    $directory->close();
    $ret[$camName]=$count;
}
echo($text=json_encode($ret));
\maierlabs\lpfw\Logger::_($text, loggerLevel::debug);




