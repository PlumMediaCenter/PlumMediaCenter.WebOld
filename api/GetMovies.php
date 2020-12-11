<?php

$basePath = dirname(__FILE__) . "/../";
include_once($basePath . "code/managers/VideoManager.php");

$videoIds = (isset($_GET["videoIds"])) ? $_GET["videoIds"] : -1;
$videoIds = explode(',', $videoIds);
$videos = VideoManager::GetVideos($videoIds);
header('Content-Type: application/json');
echo json_encode($videos, JSON_PRETTY_PRINT);
?>

