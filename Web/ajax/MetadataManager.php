<?php

include_once("../code/Library.class.php");
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
        break;
    case Enumerations::MetadataManagerAction_FetchPoster:
        break;
    case Enumerations::MetadataManagerAction_ReloadMetadata:
        break;
}






//update the library 
$l = new Library();
$l->loadFullFromJson();
$l->update($fullPath);
$l->flush();
//return the result
echo json_encode($success);
?>
