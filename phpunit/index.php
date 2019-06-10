<?php
/**
 * Created by PhpStorm.
 * User: Levi
 * Date: 07.12.2018
 * Time: 00:05
 */
include_once 'config.class.php';
include_once 'phpunit.class.php';

$pu = new \maierlabs\phpunit\phpunit();

if (isset($_GET["action"]) && $_GET["action"]="batch") {
    include_once 'PHPUnit_Framework_TestCase.php';
    header('Content-Type: text/plain');
    echo(\maierlabs\phpunit\config::$SiteTitle."\n");

    $allTests=0;
    $testFiles=$pu->getDirContents(\maierlabs\phpunit\config::$startDir,\maierlabs\phpunit\config::$excludeFiles );
    foreach ($testFiles as $idx => $testFile) {
        $tests = $pu->getTestClassMethodsFromFile($testFile["dir"].$testFile["file"]);
        $testFiles[$idx]["tests"]=sizeof($tests);
        $allTests +=sizeof($tests);
    }
    echo("test files:".sizeof($testFiles).' tests:'.$allTests);
    $allTime =0;
    $allTestsError =0;
    $allTestsOk =0;
    set_time_limit(120);

    foreach ($testFiles as $idx => $testFile) {
        $tests = $pu->getTestClassMethodsFromFile($testFile["dir"] . $testFile["file"]);

        include $testFile["dir"] . $testFile["file"];

        $testClassName=substr($testFile["file"],0,strpos(strtolower($testFile["file"]),".php"));
        $testMethodList = $pu->getTestClassMethods($testClassName);
        $testSetupMethod= $pu->getTestClassSetupMethod($testClassName);
        $testTearDownMethod= $pu->getTestClassTearDownMethod(($testClassName));

        foreach ($testMethodList as $idx => $aktTest) {
            echo("\n\n".$testClassName.'/'.$testMethodList[$idx]);
            $timer=microtime(true);
            $error=null;
            if ($idx == 0) {
                $theTestClass = new $testClassName();
            }

            error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
            ob_start();

            if ($testSetupMethod != null) {
                try {
                    $theTestClass->$testSetupMethod();
                } catch (\Exception $e) {
                    $error=$e;
                } catch (\Error $e) {
                    $error=$e;
                } catch (\Throwable $e) {
                    $error=$e;
                }
            }

            if (isset($testMethodList[$idx])) {
                try {
                    $functionName = $testMethodList[$idx];
                    $theTestClass->$functionName();
                } catch (\Exception $e) {
                    $error=$e;
                } catch (\Error $e) {
                    $error=$e;
                } catch (\Throwable $e) {
                    $error=$e;
                }
            }

            if ($testTearDownMethod != null) {
                try {
                    $theTestClass->$testTearDownMethod();
                } catch (\Exception $e) {
                    $error=$e;
                } catch (\Error $e) {
                    $error=$e;
                } catch (\Throwable $e) {
                    $error=$e;
                }
            }


            echo (ob_get_clean());
            $res = $theTestClass->assertGetUnitTestResult();
            echo( " ok:".$res->assertOk);
            echo( " error:".$res->assertError);
            echo( " time:".number_format((microtime(true) - $timer) * 1000, 2)).'ms';
            $allTime +=(microtime(true) - $timer)*1000;
            $allTestsError +=$res->assertError;
            $allTestsOk +=$res->assertOk;
        }
    }
    echo("\n\nResult ok:".$allTestsOk." error:".$allTestsError." time:".number_format($allTime,2)."ms");
    if (isset($_GET["succesmail"]) && $allTestsError==0) {
        if (sendTestResultsMail($_GET["succesmail"],"Result OK",$allTestsOk,$allTestsError,$allTime))
            echo("\nSuccesmail sent to ".$_GET["succesmail"]);
        else
            echo("\nError sending succesmail to ".$_GET["succesmail"]);
    }
    if (isset($_GET["errormail"]) && $allTestsError>0) {
        if (sendTestResultsMail($_GET["errorsmail"],"Result ERROR",$allTestsOk,$allTestsError,$allTime))
            echo("\nErrormail sent to ".$_GET["errormail"]);
        else
            echo("\nError sending errormail to ".$_GET["errormail"]);
    }
    die();
}

function sendTestResultsMail($recipient,$subject,$ok,$error,$time) {
    $subject = \maierlabs\phpunit\config::$SiteTitle.' '.$subject;
    $text = 'Reasults ok:'.$ok." error:".$error. " time:".number_format($time,2)."ms";
    $header = 'From: ' . \maierlabs\phpunit\config::$senderMail. "\r\n" .
        'Reply-To: ' . \maierlabs\phpunit\config::$senderMail. "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    return mail($recipient, $subject, $text, $header);
}

?>
<html>
    <header>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="//www.gstatic.com/charts/loader.js"></script>
    </header>
    <body>
        <div class="container-fluid well">
            <h3><?php echo(\maierlabs\phpunit\config::$SiteTitle)?></h3>
            <div style="position: relative;top:-13px;">&copy; MaierLabs version:<?php echo (\maierlabs\phpunit\config::$version)?></div>
            <div class="panel-body">
                <div><button class="btn btn-success" onclick="getTestFiles()">Check site for tests</button>
                <button class="btn btn-success" onclick="runAlltests()">Run all unit tests</button></div>
                <div id="filesGauge" style="display: inline-block;width: 700px; height: 400px;"></div>
                <div id="fileGauge" style="display: inline-block; width: 700px; height: 400px;"></div>
            </div>
            <div class="panel-body">
                <div>
                    Files:<span class="badge" style="background-color: green" id="fok">0</span><span class="badge" style="background-color: red" id="ferror">0</span>
                    Tests:<span class="badge" style="background-color: green" id="tok">0</span><span class="badge" style="background-color: red" id="terror">0</span>
                    Asserts:<span class="badge" style="background-color: green" id="aok">0</span><span class="badge" style="background-color: red" id="aerror">0</span>
                </div>
            </div>
            <div class="panel-body" id="console">
                <b>Console</b>
            <div>
        </div>
    </body>
</html>

<script type="text/javascript">
    <?php include "js/phpunit.js"?>
</script>