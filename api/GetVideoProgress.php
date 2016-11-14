<?php

include_once(dirname(__FILE__) . "/../code/Video.class.php");
$videoId = isset($_GET["videoId"]) ? $_GET["videoId"] : -1;
$seconds = Video::GetVideoStartSeconds($videoId);
$result = (object) [];
$result->videoId = $videoId;
$result->startSeconds = $seconds;
echo json_encode($result);
?>
