<?php
include_once(dirname(__FILE__) . '/../code/database/Queries.class.php');
include_once(dirname(__FILE__) . '/../config.php');
header('Content-Type: application/json');

$userId = isset($_GET["userId"]) ? $_GET["userId"] : config::$defaultUserId;
$listNames = Queries::GetVideoListInfo($userId, $_GET["videoId"]);
echo json_encode($listNames);
