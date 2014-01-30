<?php

require_once(dirname(__FILE__) . '/../code/Security.class.php');
require_once(dirname(__FILE__) . '/../code/Playlist.class.php');

$playlistName = isset($_GET["playlistName"]) ? $_GET["playlistName"] : "";
$playlistItemId = isset($_GET["playlistItemId"]) ? $_GET["playlistItemId"] : "";

//remove the first item from the playlist
Playlist::RemoveItem(Security::GetUsername(), $playlistName, $playlistItemId);
echo json_encode($video, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
?>
