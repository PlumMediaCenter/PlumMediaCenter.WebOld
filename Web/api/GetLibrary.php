<?php

$basePath = dirname(__FILE__) . "/../";
include_once($basePath . "code/DbManager.class.php");
include_once($basePath . "code/Enumerations.class.php");
include_once($basePath . "code/Video.class.php");

$library = [];

$baseQuery = Video::baseQuery;
$library["movies"] = DbManager::Query("$baseQuery where media_type = '" . Enumerations::MediaType_Movie . "' order by title asc");
$library["tvShows"] = DbManager::Query("$baseQuery where media_type =  '" . Enumerations::MediaType_TvShow . "'  order by title asc");

echo json_encode($library, JSON_PRETTY_PRINT);
?>
