<?php

include_once(dirname(__FILE__) . "/../code/database/Queries.class.php");

$videoSources = Queries::getVideoSources();
$videoSources = PropertyMappings::MapMany($videoSources, PropertyMappings::$videoSourceMapping);
header('Content-Type: application/json');
echo json_encode($videoSources);
?>
