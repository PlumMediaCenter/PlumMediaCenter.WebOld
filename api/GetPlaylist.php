<?php

require_once(dirname(__FILE__) . '/../code/Playlist.class.php');

$userId = isset($_GET["userId"]) ? $_GET["userId"] : config::$globalUserId;
$playlistName = isset($_GET["playlistName"]) ? $_GET["playlistName"] : "";

$p = new Playlist($userId, $playlistName);
$p->loadFromDb();

echo json_encode($p->getPlaylistVideos());
?>
