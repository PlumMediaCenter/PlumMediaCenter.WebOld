<?php

$basePath = dirname(__FILE__) . "/../";
include_once($basePath . "controllers/VideoController.php");

$videoIds = (isset($_GET["videoIds"])) ? $_GET["videoIds"] : -1;
$videoIds = explode(',', $videoIds);
$videos = VideoController::GetMovies($videoIds);
header('Content-Type: application/json');
echo json_encode($videos, JSON_PRETTY_PRINT);
?>

