<?php

$startSeconds = time();
//allow up to half hour for this script to run
set_time_limit(1800);
$result = (object) [];
require_once(dirname(__FILE__) . '/../code/Library.class.php');

$l = new Library();
$result->successLoadingFromDatabase = $l->loadFromDatabase();
$l->fetchAndLoadMetadata(true);

$result->success = $result->successLoadingFromDatabase && true;
// header('Content-Type: application/json');

echo json_encode($result);

$endSeconds = time();
$length = $endSeconds - $startSeconds;

//file_put_contents(dirname(__FILE__) . '/generateTime.txt', "$length\n", FILE_APPEND);
?>
