<?php

$basePath = dirname(__FILE__) . "/../";
include_once($basePath . "code/controllers/VideoController.php");

$videoId = (isset($_GET["videoId"])) ? $_GET["videoId"] : -1;
$videos = VideoController::GetTvEpisodesByShowVideoId($videoId);
header('Content-Type: application/json');
echo json_encode($videos, JSON_PRETTY_PRINT);
?>

