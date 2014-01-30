<?php

$basePath = dirname(__FILE__) . "/../";
include_once($basePath . "code/DbManager.class.php");
include_once($basePath . "code/Enumerations.class.php");
include_once($basePath . "code/Video.class.php");

$videoId = (isset($_GET["videoId"])) ? $_GET["videoId"] : -1;
/* $show TvShow */
$show = Video::GetVideo($videoId);
$show->loadEpisodesFromDatabase();
echo json_encode($show, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
?>

