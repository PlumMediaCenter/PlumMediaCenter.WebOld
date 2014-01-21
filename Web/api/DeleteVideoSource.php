<?php

include_once(dirname(__FILE__) . "/../code/database/Queries.class.php");
include_once(dirname(__FILE__) . "/../code/Video.class.php");


$sourcePath = isset($_GET["sourcePath"]) ? $_GET["sourcePath"] : "";
$success = true;
$success = Queries::DeleteVideoSource($sourcePath);
//delete every video found in the source
$videoIds = Queries::GetVideoIdsInSource($sourcePath);
foreach ($videoIds as $videoId) {
    $deleteSuccess = Video::DeleteVideo($videoId);
    $success = $success && $deleteSuccess;
}

echo json_encode($success);
?>
