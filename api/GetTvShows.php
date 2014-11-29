<?php

$basePath = dirname(__FILE__) . "/../";
include_once($basePath . "code/DbManager.class.php");
include_once($basePath . "code/Enumerations.class.php");
include_once($basePath . "code/Library.class.php");


$l = new Library();
$movies = $l->loadTvShowsFromDatabase(false);
header('Content-Type: application/json');

echo json_encode($movies, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
?>