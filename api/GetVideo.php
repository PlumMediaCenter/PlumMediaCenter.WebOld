<?php

include_once(dirname(__FILE__) . "/../code/managers/VideoManager.php");

$videoId = (isset($_GET["videoId"])) ? $_GET["videoId"] : -1;
$video = VideoManager::GetVideo($videoId);
header('Content-Type: application/json');

echo json_encode($video, JSON_PRETTY_PRINT);
