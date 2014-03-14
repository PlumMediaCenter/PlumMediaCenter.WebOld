<?php
require_once(dirname(__FILE__) . '/../config.php');
require_once(dirname(__FILE__) . '/../Code/Enumerations.class.php');
require_once(dirname(__FILE__) . '/../Core/Functions.php');
require_once(dirname(__FILE__) . '/simpletest/autorun.php');

require_once(dirname(__FILE__) . '/../Code/lib/php-activerecord/ActiveRecord.php');

//set up database stuff
ActiveRecord\Config::initialize(function($cfg) {
    $cfg->set_model_directory(dirname(__FILE__) . '/../Code/');
    $cfg->set_connections(array(
        //'development' => 'mysql://username:password@localhost/database_name',
        'development' => 'mysql://' .
        config::$dbUsername . ':' .
        config::$dbPassword . '@' .
        config::$dbHost . '/' .
        config::$dbName));
});


$test = new TestSuite('Video test');

$test->addFile(dirname(__FILE__) . '/Code/TestVideoSource.php');
//$test->addFile(dirname(__FILE__) . '/Code/TestMovie.php');

//$test->addFile(dirname(__FILE__) . '/Code/NfoReader/TestMovieNfoReader.php');
//$test->addFile(dirname(__FILE__) . '/Code/NfoReader/TestTvEpisodeNfoReader.php');
//$test->addFile(dirname(__FILE__) . '/Code/NfoReader/TestTvShowNfoReader.php');
//$test->addFile(dirname(__FILE__) . '/Code/database/TestTable.php');

//$test->addFile(dirname(__FILE__) . '/TestFunctions.php');
?>
