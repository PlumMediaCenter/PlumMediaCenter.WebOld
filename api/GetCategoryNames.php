<?php
include_once(dirname(__FILE__) . '/../code/managers/VideoCategoryManager.php');

$categoryNames = VideoCategoryManager::GetCategoryNames();

header('Content-Type: application/json');

echo json_encode($categoryNames);
?>
