<?php

require_once(dirname(__FILE__) . '/../code/Playlist.class.php');
require_once(dirname(__FILE__) . '/../code/Security.class.php');

$playlistName = isset($_GET["playlistName"]) ? $_GET["playlistName"] : "";

//save changes
echo Playlist::DeletePlaylist(Security::GetUsername(), $playlistName);
?>
