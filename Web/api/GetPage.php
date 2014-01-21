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

$p = new Playlist($username, $playlistName);
//load from the database
$p->loadFromDb();
//append any new videos
$p->addRange($videoIds);
//save changes
echo $p->writeToDb();
?>
