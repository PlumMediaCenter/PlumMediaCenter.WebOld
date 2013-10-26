<?php

include_once(dirname(__FILE__) . "/../code/database/Queries.class.php");
include_once(dirname(__FILE__) . "/../code/Video.class.php");

include_once(dirname(__FILE__) . "/../config.php");

//$username = $_GET["username"];
$videoId = isset($_GET["videoId"]) ? $_GET["videoId"] : -1;
$timeInSeconds = isset($_GET["seconds"]) ? $_GET["seconds"] : 0;
$finished = isset($_GET["finished"]) ? $_GET["finished"] : false;
//if the finished flag was set, retrieve the total length of this video and save THAT time in the watchVideo table so we know this video is finished
if ($finished === "true") {
    $v = Video::GetVideo($videoId);
    $sec = $v->getLengthInSeconds();
    //if the length was determined, use it
    if ($sec !== false) {
        $timeInSeconds = $sec;
    } else {
        //set the time in seconds to be negative so we know this video is finished, even though we don't know what the actual length is
        $timeInSeconds = -1;
    }
}
$success = Queries::insertWatchVideo(config::$globalUsername, $videoId, $timeInSeconds);
$result = (object) [];
$result->success = $success;
echo json_encode($result);
?>
