<?php

//ob_start();
require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../code/functions.php');

$test = new TestSuite('Video test');

$specificTestToRun = isset($_GET['file']) ? $_GET['file'] : null;

//find every file inside of the tests folder

$testFiles = getFilesFromDir(dirname(__FILE__) . '/tests/');

foreach ($testFiles as $testFile) {
    if ($specificTestToRun === null) {
        $test->addFile($testFile);
    } else {
        //a specific test was specified. Only run that test
        $currFilenameAndExt = pathinfo($testFile, PATHINFO_FILENAME) . '.' . pathinfo($testFile, PATHINFO_EXTENSION);
        if ($currFilenameAndExt === $specificTestToRun) {
            $test->addFile($testFile);
        }
    }
}

//echo ob_get_contents();
?>
