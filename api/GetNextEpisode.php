<?php

include_once(dirname(__FILE__) . "/../code/TvShow.class.php");
include_once(dirname(__FILE__) . "/../controllers/VideoController.php");
;


$tvSeriesVideoId = isset($_GET["videoId"]) ? $_GET["videoId"] : -1;
$episode = TvShow::GetNextEpisodeToWatch($tvSeriesVideoId);
$episode = VideoController::GetEpisode($episode->videoId);
header('Content-Type: application/json');
$episode->startSeconds = $episode->videoStartSeconds();
echo json_encode($episode);
?>
