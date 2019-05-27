<?php

require_once('../code/Security.class.php');
require_once('../code/database/Queries.class.php');

$listName = isset($_GET["listName"]) ? $_GET["listName"] : "";
$videoId = intval($_GET["videoId"]);
$isInList = Queries::IsInList($listName, $videoId, Security::GetUserId());

header('Content-Type: application/json');
echo json_encode($isInList);
