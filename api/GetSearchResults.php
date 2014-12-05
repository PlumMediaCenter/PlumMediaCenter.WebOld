<?php

require_once(dirname(__FILE__) . '/../controllers/VideoController.php');

$title = isset($_GET["title"]) ? $_GET["title"] : '';
$videos = VideoController::SearchByTitle($title);
header('Content-Type: application/json');
echo json_encode($videos);
?>
