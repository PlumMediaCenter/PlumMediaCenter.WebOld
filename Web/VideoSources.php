<?php

include_once("code/Page.class.php");
include_once("code/database/Queries.class.php");

//if the delete source button was pressed, delete this video source
if (isset($_GET["deleteSource"]) && strlen($_GET["deleteSource"]) > 0) {
    Queries::deleteVideoSource($_GET["deleteSource"]);
}

//if the add/edit source button was pressed, add/edit this video source
if (isset($_GET["addEditSource"])) {
    $location = $_GET["location"];
    $baseUrl = $_GET["baseUrl"];
    $mediaType = $_GET["mediaType"];
    $securityType = $_GET["securityType"];
    Queries::addVideoSource($location, $baseUrl, $mediaType, $securityType);
}
$p = new Page(__FILE__);
$m = $p->getModel();
$m->videoSources = Queries::getVideoSources();
$p->show();
?>