<?php

require_once(dirname(__FILE__) . '/../code/Security.class.php');
require_once(dirname(__FILE__) . '/../code/Playlist.class.php');

$playlistName = isset($_GET["playlistName"]) ? $_GET["playlistName"] : "";
$video = Playlist::GetFirstVideo(Security::GetUsername(), $playlistName);
echo json_encode($video, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
?>
