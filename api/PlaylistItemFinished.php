<?php

require_once(dirname(__FILE__) . '/../code/Security.class.php');
require_once(dirname(__FILE__) . '/../code/Playlist.class.php');

$playlistName = isset($_GET["playlistName"]) ? $_GET["playlistName"] : "";
$playlistItemId = isset($_GET["playlistItemId"]) ? $_GET["playlistItemId"] : "";

//remove the first item from the playlist
Playlist::RemoveItem(Security::GetUserId(), $playlistName, $playlistItemId);
echo json_encode($video);
?>
