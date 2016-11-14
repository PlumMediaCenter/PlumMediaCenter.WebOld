<?php

require_once(dirname(__FILE__) . '/../code/Playlist.class.php');

$userId = isset($_GET["userId"]) ? $_GET["userId"] : config::$globalUserId;

echo json_encode(Playlist::GetPlaylists($userId));
?>
