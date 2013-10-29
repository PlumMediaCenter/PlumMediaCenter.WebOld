<?php

include_once("code/Page.class.php");
include_once("code/Video.class.php");


$p = new Page(__FILE__);
$m = $p->getModel();
$videoId = isset($_GET["videoId"]) ? $_GET["videoId"] : -1;
$video = Video::GetVideo($videoId);
//if the video is a tv episode, load the tv show and load the SHOW page instead
if ($video->getMediaType() === Enumerations::MediaType_TvEpisode) {
    $video = Video::GetVideo($video->getTvShowVideoIdFromVideoTable());
}
if ($video->getMediaType() === Enumerations::MediaType_TvShow) {
    $video->setLoadEpisodesFromDatabase(true);
    $video->generateTvEpisodes();
    $video->retrieveVideoIds();
}
$m->video = $video;
$m->title = "PVP $video->title Info";
$m->videoJson = json_encode($m->video);
$p->show();
?>