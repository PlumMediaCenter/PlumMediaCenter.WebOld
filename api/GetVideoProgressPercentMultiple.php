<?php

include_once(dirname(__FILE__) . '/../code/Video.class.php');
$videoIds = isset($_GET['videoIds']) ? explode(',', $_GET['videoIds']) : [-1];
$results = [];
foreach ($videoIds as $videoId) {
    $percent = Video::GetVideoProgressPercent($videoId);
    $results[] = (object) [
                'videoId' => intval($videoId),
                'percent' => $percent
    ];
}

header('Content-Type: application/json');
echo json_encode($results);
?>
