<?php

$basePath = dirname(__FILE__) . "/../";
include_once($basePath . "code/controllers/VideoController.php");

$videoId = (isset($_GET["videoId"])) ? $_GET["videoId"] : -1;
$video = VideoController::GetTvShowByEpisodeId($videoId);
header('Content-Type: application/json');
echo json_encode($video, JSON_PRETTY_PRINT);
?>