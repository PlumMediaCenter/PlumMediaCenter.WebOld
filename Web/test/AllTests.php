<?php

require_once(dirname(__FILE__) . '/simpletest/autorun.php');

$test = new TestSuite('Video test');
$test->addFile(dirname(__FILE__) . '/TestCode/TestMovie.php');
$test->addFile(dirname(__FILE__) . '/TestCode/TestMovieNfoReader.php');
$test->addFile(dirname(__FILE__) . '/TestCode/database/TestTable.php');


//$test->addFile(dirname(__FILE__) . '/TestFunctions.php');
?>
