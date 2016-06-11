<?php

require_once(dirname(__FILE__) . '/../code/LibraryGenerator.class.php');

$videoId = isset($_GET['videoId']) ? $_GET['videoId'] : null;
if ($videoId == null) {
    throw new Exception('no videoId provided');
}

$path = null;
$video = Video::GetVideo($videoId);
if ($video->mediaType === Enumerations::MediaType_Movie) {
    $path = Video::GetVideoFullPathToContainingFolder($video->getFullPath());
} else if ($video->mediaType === Enumerations::MediaType_TvShow) {
    $path = $video->getFullPath();
} else if ($video->mediaType === Enumerations::MediaType_TvShow) {
    /* @var TvEpisode $episode */
    $episode = $video;
    $show = $episode->getTvShowObject();
    $path = $show->getFullPath();
}

$newVideoIds = LibraryGenerator::AddNewMediaItem(null, $path);
$result = (object) ['success' => true, 'newVideoIds' => $newVideoIds];
echo json_encode($result);
?>
