<?php

include_once(dirname(__FILE__) . "/../code/database/Queries.class.php");
$id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$id) {
    throw new Exception('id was not provided');
}

sleep(3);
$videoSources = Queries::GetVideoSourcesById([$id]);
//if no sources were found, return a 404
if (count($videoSources) != 1) {
    header("HTTP/1.0 404 Not Found");
} else {
    $videoSource = $videoSources[0];
    $mappedVideoSource = PropertyMappings::MapOne($videoSource, PropertyMappings::$videoSourceMapping);
    header('Content-Type: application/json');
    echo json_encode($mappedVideoSource);
}
?>
