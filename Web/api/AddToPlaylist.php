<?php

require_once(dirname(__FILE__) . '/../code/Playlist.class.php');
$username = isset($_GET["username"]) ? $_GET["username"] : config::$globalUsername;
$playlistName = isset($_GET["playlistName"]) ? $_GET["playlistName"] : "";
$videoIds = isset($_GET["videoIds"]) ? explode(",",$_GET["videoIds"]) : [];

$p = new Playlist($username, $playlistName);
//load from the database
$p->loadFromDb();
//append any new videos
$p->addRange($videoIds);
//save changes
echo $p->writeToDb();
?>
