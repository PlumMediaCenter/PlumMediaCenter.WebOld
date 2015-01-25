<?php

include_once("../code/controllers/VideoController.php");
$videoId = isset($_GET['videoId']) ? $_GET['videoId'] : null;
$onlineVideoId = isset($_GET['onlineVideoId']) ? $_GET['onlineVideoId'] : null;
$success = VideoController::FetchMetadata($videoId, $onlineVideoId);
echo json_encode($success);
?>
