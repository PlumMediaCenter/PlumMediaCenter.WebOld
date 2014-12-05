<?php

$basePath = dirname(__FILE__) . "/../";
include_once($basePath . "controllers/VideoController.php");

$videoId = (isset($_GET["videoId"])) ? $_GET["videoId"] : -1;
$video = VideoController::GetMovie($videoId);
header('Content-Type: application/json');
echo json_encode($video, JSON_PRETTY_PRINT);
?>

