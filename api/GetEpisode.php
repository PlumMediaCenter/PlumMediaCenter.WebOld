<?php

include_once(dirname(__FILE__) . "/../code/TvShow.class.php");
include_once(dirname(__FILE__) . "/../code/managers/VideoManager.php");

$videoId = isset($_GET["videoId"]) ? $_GET["videoId"] : -1;
$episode = VideoManager::GetTvEpisode($videoId);
header('Content-Type: application/json');

$episode->startSeconds = Video::GetVideoStartSeconds($episode->videoId);
echo json_encode($episode);
?>
