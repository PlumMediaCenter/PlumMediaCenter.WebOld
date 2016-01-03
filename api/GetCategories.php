<?php

include_once(dirname(__FILE__) . '/../code/Library.class.php');

$names = isset($_GET['names']) ? $_GET['names'] : '';
$namesArray = explode(',', $names);
$categories = Library::GetCategories($namesArray);

header('Content-Type: application/json');

echo json_encode($categories);
?>
