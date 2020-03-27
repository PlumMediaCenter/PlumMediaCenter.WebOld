<?php

include_once(dirname(__FILE__) . '/../code/Library.class.php');
include_once(dirname(__FILE__) . '/../code/functions.php');

$names = isset($_GET['names']) ? $_GET['names'] : null;
if ($names) {
    $namesArray = explode(',', $names);
} else {
    $namesArray = Library::GetCategoryNames();
}
$result = Library::GetCategories($namesArray);

if (isset($_GET['properties'])) {
    $properties = explode(',', $_GET['properties']);
    $result->videos = filterProperties($result->videos, $properties);
}
header('Content-Type: application/json');
echo json_encode($result);
