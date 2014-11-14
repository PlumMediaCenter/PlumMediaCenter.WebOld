<?php
require_once(dirname(__FILE__) . '/../code/Video.class.php');

$title = isset($_GET["title"]) ? $_GET["title"] : '';
$videos = Video::searchByTitle($title);
$videos = Video::PrepareVideosForJsonification($videos);
header('Content-Type: application/json');
echo json_encode($videos);

?>
