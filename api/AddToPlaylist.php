<?php

require_once(dirname(__FILE__) . '/../code/Playlist.class.php');
$userId = isset($_GET["userId"]) ? $_GET["userId"] : config::$defaultUserId;
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

$p = new Playlist($userId, $playlistName);
//load from the database
$p->loadFromDb();
//append any new videos
$p->addRange($videoIds);
//save changes
echo $p->writeToDb();
?>
