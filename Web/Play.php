<?php

include("code/Page.class.php");

$p = new Page(__FILE__);
$m = $p->getModel();
//if a playlist name is present in the url, get the videoId of the first video in the playlist
if(isset($_GET["playlistName"])){
    //$p = new Playlist($_GET["playlistName"]);
}
$videoId = isset($_GET["videoId"]) ? $_GET["videoId"] : -1;
$m->init($videoId);
$p->show();
?>