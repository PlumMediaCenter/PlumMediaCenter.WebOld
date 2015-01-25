<?php

include_once(dirname(__FILE__) . '/../code/DbManager.class.php');

$videoId = (isset($_GET['videoId'])) ? intval($_GET['videoId']) : -1;
$results = DbManager::GetAllClassQuery(
                        "select path, video_source_path, media_type from video where video_id=$videoId");
$video = null;
if(count($results) === 1){
    $row = $results[0];
    $video = (object)[
        'videoId'=>$videoId, 
        'path'=>$row->path, 
        'sourcePath'=>$row->video_source_path, 
        'mediaType'=>$row->media_type
    ];
}
header('Content-Type: application/json');

echo json_encode($video);
?>

