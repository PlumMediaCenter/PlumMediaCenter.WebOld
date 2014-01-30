<?php

require_once(dirname(__FILE__) . '/../code/Playlist.class.php');

$username = isset($_GET["username"]) ? $_GET["username"] : config::$globalUsername;

echo json_encode(Playlist::GetPlaylists($username), JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
?>
