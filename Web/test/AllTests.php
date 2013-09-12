<?php

require_once(dirname(__FILE__) . '/simpletest/autorun.php');

$test = new TestSuite('Video test');
$test->addFile(dirname(__FILE__) . '/TestMovie.php');
$test->addFile(dirname(__FILE__) . '/TestMovieNfoReader.php');

//$test->addFile(dirname(__FILE__) . '/TestFunctions.php');
?>
