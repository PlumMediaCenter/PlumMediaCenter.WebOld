<?php

include_once(dirname(__FILE__) . "/../code/TvShow.class.php");

$tvSeriesVideoId = isset($_GET["videoId"]) ? $_GET["videoId"] : -1;
$episodeVideoId = TvShow::getNextEpisodeToWatch($tvSeriesVideoId);
$result = (object) [];
$result->videoId = $episodeVideoId;
$result->startSeconds = Video::getVideoStartSeconds($episodeVideoId);
echo json_encode($result);
?>
