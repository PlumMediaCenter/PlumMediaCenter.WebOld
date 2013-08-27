<?php

include_once(dirname(__FILE__) . "/../code/database/Queries.class.php");
$username = $_GET["username"];
$videoId = $_GET["videoId"];
$currentTimeSeconds = $_GET["seconds"];
$positionInBytes = $_GET["bytes"];
Queries::insertWatchVideo('user', 1, 20, 500);
return json_encode(true);
?>
