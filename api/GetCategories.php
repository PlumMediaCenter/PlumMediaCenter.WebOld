<?php

include_once(dirname(__FILE__) . '/../code/managers/VideoCategoryManager.php');

$categoryNames = isset($_GET['names']) ? explode(',', $_GET['names']) : null;
$propertyNames = isset($_GET['properties']) ? explode(',', $_GET['properties']) : null;

$result = VideoCategoryManager::GetCategories($categoryNames, $propertyNames);

header('Content-Type: application/json');
echo json_encode($result);
