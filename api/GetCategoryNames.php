<?php
include_once(dirname(__FILE__) . '/../code/Library.class.php');

$categoryNames = Library::GetCategoryNames();

header('Content-Type: application/json');

echo json_encode($categoryNames);
?>
