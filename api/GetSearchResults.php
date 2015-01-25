<?php

require_once(dirname(__FILE__) . '/../code/controllers/VideoController.php');

$query = isset($_GET["q"]) ? $_GET["q"] : '';
$videos = VideoController::SearchByTitle($query);
header('Content-Type: application/json');
echo json_encode($videos);
?>
