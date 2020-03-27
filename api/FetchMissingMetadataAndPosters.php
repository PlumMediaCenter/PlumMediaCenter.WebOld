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

    if ($video->fetchMetadataIfMissing() === true) {
        //force the video to load metadata from the FS
        $video->loadMetadata(true);
        //write the video to the database
        $video->writeToDb();
    }

    
    //if this is a tv show, write all of its children
    if ($video->mediaType === Enumerations::MediaType_TvShow) {
        $video->loadTvEpisodesFromFilesystem();
        $episodes = $video->getEpisodes();
        foreach ($episodes as $episode) {
            if ($episode->fetchMetadataIfMissing()) {
                $episode->loadMetadata(true);
                $episode->writeToDb();
            }
        }
    }
}

$result->success = $result->successLoadingFromDatabase && true;
// header('Content-Type: application/json');

echo json_encode($result);

$endSeconds = time();
$length = $endSeconds - $startSeconds;

//file_put_contents(dirname(__FILE__) . '/generateTime.txt', "$length\n", FILE_APPEND);
?>
