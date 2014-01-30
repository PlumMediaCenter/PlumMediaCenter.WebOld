<?php

include_once("../code/functions.php");

$baseUrl = $_GET["baseUrl"];
$basePath = $_GET["basePath"];
$fullPath = $_GET["fullPath"];
$mediaType = $_GET["mediaType"];
//import the proper class
include_once("../code/$mediaType.class.php");
/* @var $v Video */
$v = new $mediaType($baseUrl, $basePath, $fullPath);
$success = false;

switch ($_GET["action"]) {
    case Enumerations::MetadataManagerAction_GeneratePosters:
        //generate the posters
        $success = $v->generateSdPoster();
        $success = $success && $v->generateHdPoster();
        break;
    case Enumerations::MetadataManagerAction_FetchMetadata:
        $success = $v->fetchMetadata();
        break;
    case Enumerations::MetadataManagerAction_FetchPoster:
        $success = $v->fetchPoster();
        break;
    case Enumerations::MetadataManagerAction_ReloadMetadata:
        $success = $v->writeToDb();
        break;
    case Enumerations::MetadataManagerAction_FetchAndGeneratePosters:
        $success = $v->fetchPoster();
        //generate the posters
        $success = $success && $v->generateSdPoster();
        $success = $success && $v->generateHdPoster();
        break;
}
$result = (object) [];
$result->success = $success;
//return the result
if ($success === true) {
    //return the new video data to be put into the 
    $v = new $mediaType($baseUrl, $basePath, $fullPath);
    //load the latest metadata from the file into the video 
    $v->loadMetadata(true);
    $result->output = getVideoMetadataRow($v);
} else {
    
}
echo json_encode($result, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
?>
