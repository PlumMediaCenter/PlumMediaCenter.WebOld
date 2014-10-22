<?php

include_once(dirname(__FILE__) . "/../code/TvShow.class.php");


$tvSeriesVideoId = isset($_GET["videoId"]) ? $_GET["videoId"] : -1;
$episode = TvShow::GetNextEpisodeToWatch($tvSeriesVideoId);
//header('Content-Type: application/json');
if ($episode == null) {
    echo json_encode(false);
} else {
    $episode->startSeconds = $episode->videoStartSeconds();
    echo json_encode($episode);
}
?>
