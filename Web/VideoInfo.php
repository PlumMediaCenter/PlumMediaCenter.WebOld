<?php
include_once("code/Page.class.php");
include_once("code/Video.class.php");


$p = new Page(__FILE__);
$m = $p->getModel();
$videoId = isset($_GET["videoId"])? $_GET["videoId"]: -1;
$video = Video::loadFromDb($videoId);
if($video->mediaType === Enumerations::MediaType_TvShow){
    $video->setLoadEpisodesFromDatabase(true);
    $video->generateTvEpisodes();
}
$m->video = $video;
$m->videoJson = json_encode($m->video);
$p->show();


?>