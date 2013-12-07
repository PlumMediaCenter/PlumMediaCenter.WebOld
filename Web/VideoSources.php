<?php

include_once("code/Page.class.php");
include_once("code/database/Queries.class.php");

//if the delete source button was pressed, delete this video source
if (isset($_POST["deleteSource"]) && strlen($_POST["deleteSource"]) > 0) {
    Queries::deleteVideoSource($_POST["deleteSource"]);
}

//if the add/edit source button was pressed, add/edit this video source
if (isset($_POST["addSource"])) {
    $location = $_POST["location"];
    $baseUrl = $_POST["baseUrl"];
    $mediaType = $_POST["mediaType"];
    $securityType = $_POST["securityType"];
    Queries::addVideoSource($location, $baseUrl, $mediaType, $securityType);
}

//if the add/edit source button was pressed, add/edit this video source
if (isset($_POST["editSource"])) {
    $originalLocation = $_POST["originalLocation"];
    $location = $_POST["location"];
    $baseUrl = $_POST["baseUrl"];
    $mediaType = $_POST["mediaType"];
    $securityType = $_POST["securityType"];
    $success = Queries::updateVideoSource($originalLocation, $location, $baseUrl, $mediaType, $securityType);
}
$p = new Page(__FILE__);
$m = $p->getModel();
$m->videoSources = Queries::getVideoSources();
$p->show();
?>