<?php

require_once(dirname(__FILE__) . '/simpletest/autorun.php');

$test = new TestSuite('Video test');
$test->addFile(dirname(__FILE__) . '/Code/TestDbManager.php');
//$test->addFile(dirname(__FILE__) . '/Code/TestFunctions.php');
$test->addFile(dirname(__FILE__) . '/Code/TestMovie.php');
$test->addFile(dirname(__FILE__) . '/Code/NfoReader/TestMovieNfoReader.php');
$test->addFile(dirname(__FILE__) . '/Code/NfoReader/TestTvEpisodeNfoReader.php');
$test->addFile(dirname(__FILE__) . '/Code/NfoReader/TestTvShowNfoReader.php');
$test->addFile(dirname(__FILE__) . '/Code/database/TestTable.php');

//$test->addFile(dirname(__FILE__) . '/TestFunctions.php');
?>
