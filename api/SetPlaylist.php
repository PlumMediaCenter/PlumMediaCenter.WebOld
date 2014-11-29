<?php

require_once(dirname(__FILE__) . '/../code/Playlist.class.php');
$username = isset($_GET["username"]) ? $_GET["username"] : config::$globalUsername;
$playlistName = isset($_GET["playlistName"]) ? $_GET["playlistName"] : "";
$videoIds = [];
//get the videoIds. if they are in the form of an array, use the array. if they are not, create an array
if (isset($_GET["videoIds"])) {
    if (is_array($_GET["videoIds"])) {
        $videoIds = $_GET["videoIds"];
    } else {
        $videoIds[] = intval($_GET["videoIds"]);
    }
}

$success = Playlist::AddPlaylist($username, $playlistName, $videoIds);
echo json_encode((object) ["success" => $success]);
?>
