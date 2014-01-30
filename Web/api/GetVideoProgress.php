<?php

include_once(dirname(__FILE__) . "/../code/Video.class.php");
//$username = $_GET["username"];
$videoId = isset($_GET["videoId"]) ? $_GET["videoId"] : -1;
$seconds = Video::GetVideoStartSeconds($videoId);
$result = (object) [];
$result->videoId = $videoId;
$result->startSeconds = $seconds;
echo json_encode($result, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
?>
