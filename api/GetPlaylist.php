<?php

require_once(dirname(__FILE__) . '/../code/Playlist.class.php');

$username = isset($_GET["username"]) ? $_GET["username"] : config::$globalUsername;
$playlistName = isset($_GET["playlistName"]) ? $_GET["playlistName"] : "";

$p = new Playlist($username, $playlistName);
$p->loadFromDb();

echo json_encode($p->getPlaylistVideos());
?>
