<?php

include_once(dirname(__FILE__) . "/../code/Video.class.php");
//$username = $_GET["username"];
$videoId = isset($_GET["videoId"]) ? $_GET["videoId"] : -1;
$percent = Video::GetVideoProgressPercent($videoId);
$result = (object) [];
$result->videoId = $videoId;
$result->percent = $percent;
echo json_encode($result);
?>
