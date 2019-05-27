<?php

require_once(dirname(__FILE__) . '/../code/Playlist.class.php');

$userId = isset($_GET["userId"]) ? $_GET["userId"] : config::$defaultUserId;

echo json_encode(Playlist::GetPlaylistNames($userId));
?>
