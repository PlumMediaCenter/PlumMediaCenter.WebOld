<?php

include_once( dirname(__FILE__) . "/../code/managers/VideoManager.php");

$videoId = (isset($_GET["videoId"])) ? $_GET["videoId"] : -1;
$videos = VideoManager::GetTvEpisodesForShow($videoId);
header('Content-Type: application/json');
echo json_encode($videos, JSON_PRETTY_PRINT);
