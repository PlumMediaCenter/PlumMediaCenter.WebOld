<?php

include_once(dirname(__FILE__) . '/../code/database/Queries.class.php');
$_POST = (array)json_decode(file_get_contents('php://input'));

//if the add/edit source button was pressed, add/edit this video source
$id = isset($_POST['id']) ? $_POST['id'] : null;
$location = $_POST['location'];
$baseUrl = $_POST['baseUrl'];
$mediaType = $_POST['mediaType'];
$securityType = $_POST['securityType'];

if ($id) {
    //delete any videos that were in the original source....since that source has now been changed.
    //Eventually we want to figure out a way to detect if a file has been moved.
    Queries::DeleteVideosInSource($originalLocation);
    $success = Queries::UpdateVideoSource($originalLocation, $location, $baseUrl, $mediaType, $securityType);
} else {
    Queries::addVideoSource($location, $baseUrl, $mediaType, $securityType);
}

