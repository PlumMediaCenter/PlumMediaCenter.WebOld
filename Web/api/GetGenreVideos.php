<?php

include_once(dirname(__FILE__) . "/../code/DbManager.class.php");
include_once(dirname(__FILE__) . "/../code/Video.class.php");
$genreName = (isset($_GET["genreName"])) ? $_GET["genreName"] : "";
$genreVideos = DbManager::query(Video::baseQuery . " where video_id in("
                . " select video_id "
                . " from video_genre "
                . " where genre_name = '$genreName'"
                . ")");
echo json_encode($genreVideos, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
?>
