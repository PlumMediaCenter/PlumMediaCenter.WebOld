<?php
include_once("../code/functions.php");

$baseUrl = $_GET["baseUrl"];
$basePath = $_GET["basePath"];
$fullPath = $_GET["fullPath"];
$mediaType = $_GET["mediaType"];
//import the proper class
include_once("../code/$mediaType.class.php");
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
        break;
    case Enumerations::MetadataManagerAction_FetchAndGeneratePosters:
        $success = $v->fetchPoster();
        //generate the posters
        $success = $success && $v->generateSdPoster();
        $success = $success && $v->generateHdPoster();
        break;
}
//return the result
if ($success === true) {
    //return the new video data to be put into the 
    $v = new $mediaType($baseUrl, $basePath, $fullPath);
    echo printVideoMetadataRow($v);
} else {
    echo json_encode($success);
}
?>
