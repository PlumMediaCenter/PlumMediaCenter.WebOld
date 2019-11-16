<?php

include_once("../code/controllers/VideoController.php");
$videoId = isset($_GET['videoId']) ? $_GET['videoId'] : null;
$tmdbId = isset($_GET['tmdbId']) ? $_GET['tmdbId'] : null;
$success = VideoController::FetchMetadata($videoId, $tmdbId);
echo json_encode($success);
?>
