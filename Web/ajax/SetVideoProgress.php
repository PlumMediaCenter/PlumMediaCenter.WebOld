<?php

include_once(dirname(__FILE__) . "/../code/database/Queries.class.php");
//$username = $_GET["username"];
$videoId = $_GET["videoId"];
$timeInSeconds = $_GET["seconds"];
//$positionInBytes = $_GET["bytes"];
Queries::insertWatchVideo('user', $videoId, $timeInSeconds, 500);
return json_encode(true);
?>
