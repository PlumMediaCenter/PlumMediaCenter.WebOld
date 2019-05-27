<?php

require_once('../code/Security.class.php');
require_once('../code/database/Queries.class.php');

$listName = isset($_GET["listName"]) ? $_GET["listName"] : "";
$videoIds = [];
//get the videoIds. if they are in the form of an array, use the array. if they are not, create an array
if (isset($_GET["videoIds"])) {
    if (is_array($_GET["videoIds"])) {
        $videoIds = $_GET["videoIds"];
    } else {
        $videoIds[] = intval($_GET["videoIds"]);
    }
}

Queries::RemoveFromList($listName, $videoIds, Security::GetUserId());
