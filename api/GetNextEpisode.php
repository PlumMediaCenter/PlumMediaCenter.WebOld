<?php

include_once(dirname(__FILE__) . "/../code/TvShow.class.php");
include_once(dirname(__FILE__) . "/../code/managers/VideoManager.php");


$tvSeriesVideoId = isset($_GET["videoId"]) ? $_GET["videoId"] : -1;
$episode = TvShow::GetNextEpisodeToWatch($tvSeriesVideoId);
$episode = VideoManager::GetTvEpisodes([$episode->videoId])[0];
header('Content-Type: application/json');

$episode->startSeconds = Video::GetVideoStartSeconds($episode->videoId);
echo json_encode($episode);
?>
