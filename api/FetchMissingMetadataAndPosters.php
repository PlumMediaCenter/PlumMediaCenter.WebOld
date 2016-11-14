<?php

$startSeconds = time();
//allow up to half hour for this script to run
set_time_limit(1800);
$result = (object) [];
require_once(dirname(__FILE__) . '/../code/Library.class.php');

$l = new Library();
$result->successLoadingFromDatabase = $l->loadFromDatabase();
/* @var $video Video   */
foreach ($l->videos as $video) {
    //skip this video if it's not an object
    if (is_object($video) == false) {
        continue;
    }
    try {
        $changesWereMade = false;
        if ($video->nfoFileExists() == false) {
            $video->fetchMetadata();
            $changesWereMade = true;
        }
        if ($video->posterExists() == false) {
            $video->fetchPoster();
            $changesWereMade = true;
        }
        if ($video->sdPosterExists() == false || $video->hdPosterExists() == false) {
            $video->generatePosters();
            $changesWereMade = true;
        }
        if ($changesWereMade) {
            $video->writeToDb();
        }
    } catch (Exception $e) {
        
    }
}

$result->success = $result->successLoadingFromDatabase && true;
header('Content-Type: application/json');

echo json_encode($result);

$endSeconds = time();
$length = $endSeconds - $startSeconds;

//file_put_contents(dirname(__FILE__) . '/generateTime.txt', "$length\n", FILE_APPEND);
?>
