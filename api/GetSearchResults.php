<?php

require_once(dirname(__FILE__) . '/../code/managers/VideoManager.php');

$query = isset($_GET["q"]) ? $_GET["q"] : '';
$videos = VideoManager::SearchByTitle($query);
header('Content-Type: application/json');
echo json_encode($videos);
?>
