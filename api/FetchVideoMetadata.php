<?php

include_once("../code/Video.class.php");

$videoId = isset($_GET['videoId']) ? $_GET['videoId'] : null;
$onlineVideoId = isset($_GET['onlineVideoId']) ? $_GET['onlineVideoId'] : null;
$success = true;

//load the video
$video = Video::GetVideo($videoId);
if (!$onlineVideoId) {
    throw new Exception('onlineVideoId is required');
}
try {
    $video->fetchMetadata($onlineVideoId);
    $video->loadMetadata(true);
} catch (Exception $e) {
    $success = false;
}

try {
    $video->fetchPoster();
    $video->generatePosters();
} catch (Exception $ex) {
    $success = false;
}

$video->writeToDb();


echo json_encode($success);
?>
