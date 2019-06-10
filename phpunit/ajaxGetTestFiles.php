<?php
/**
 * Created by PhpStorm.
 * User: Levi
 * Date: 07.12.2018
 * Time: 10:22
 */
include 'config.class.php';
include 'phpunit.class.php';


header('Content-Type: application/json');

$pu = new \maierlabs\phpunit\phpunit();

$testFiles=$pu->getDirContents(\maierlabs\phpunit\config::$startDir,\maierlabs\phpunit\config::$excludeFiles );

foreach ($testFiles as $idx => $testFile) {
    $tests = $pu->getTestClassMethodsFromFile($testFile["dir"].$testFile["file"]);
    $testFiles[$idx]["tests"]=sizeof($tests);
}

echo json_encode($testFiles);