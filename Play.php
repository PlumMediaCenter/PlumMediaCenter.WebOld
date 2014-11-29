<?php

include("code/Page.class.php");

$p = new Page(__FILE__);
$m = $p->getModel();
$playlistName = null;
//if a playlist name is present in the url, get the videoId of the first video in the playlist
if (isset($_GET["playlistName"])) {
    $playlistName = $_GET["playlistName"];
    $m->initPlaylist($playlistName);
    $p->show();
} else {
    $videoId = isset($_GET["videoId"]) ? $_GET["videoId"] : -1;
    $m->init($videoId);
    $p->show();
}
?>