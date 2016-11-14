<?php

include_once(dirname(__FILE__) . "/../code/database/Queries.class.php");
include_once(dirname(__FILE__) . "/../code/Video.class.php");

include_once(dirname(__FILE__) . "/../config.php");

//$userId = $_GET["userId"];
$videoId = isset($_GET["videoId"]) ? $_GET["videoId"] : -1;
$timeInSeconds = isset($_GET["seconds"]) ? $_GET["seconds"] : 0;
$finished = isset($_GET["finished"]) ? $_GET["finished"] : false;
$v = Video::GetVideo($videoId);
    
//if the finished flag was set, retrieve the total length of this video and save THAT time in the watchVideo table so we know this video is finished
if ($finished === "true") {
    $sec = $v->getLengthInSeconds();
    //if the length was determined, use it
    if ($sec !== false) {
        $timeInSeconds = $sec;
    } else {
        //set the time in seconds to be negative so we know this video is finished, even though we don't know what the actual length is
        $timeInSeconds = -1;
    }
}
//insert into the watch_video table
$success = Queries::InsertWatchVideo(config::$globalUserId, $videoId, $timeInSeconds);

//if this is a tv episode, retrieve its show videoId
if($v->mediaType == Enumerations::MediaType_TvEpisode){
    $videoId = $v->getTvShowVideoIdFromTvEpisodeTable();
}

//insert into the recently_watched table so we don't have to calculate recently watched
Queries::InsertRecentlyWatched(config::$globalUserId, $videoId);

$result = (object) [];
$result->success = $success;
echo json_encode($result);
?>
