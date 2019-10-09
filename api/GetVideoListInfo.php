<?php
include_once(dirname(__FILE__) . '/../code/database/Queries.class.php');
include_once(dirname(__FILE__) . '/../config.php');
header('Content-Type: application/json');

$userId = isset($_GET["userId"]) ? $_GET["userId"] : config::$defaultUserId;
$videoId = isset($_GET["videoId"]) ? $_GET["videoId"] : null;
$listNames = Queries::GetVideoListInfo($userId, $videoId);
echo json_encode($listNames);
