<?php

$basePath = dirname(__FILE__) . "/../";
include_once($basePath . "code/controllers/VideoController.php");

$videoIds = isset($_GET['videoIds']) ? $_GET['videoIds'] : '';
if (strlen($videoIds) === 0) {
    $videoIds = [];
} else {
    $videoIds = explode(',', $videoIds);
}

$videos = VideoController::GetTvShows($videoIds);
header('Content-Type: application/json');

echo json_encode($videos, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
?>
