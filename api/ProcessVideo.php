<?php

require_once(dirname(__FILE__) . '/../code/LibraryGenerator.class.php');
$videoId = isset($_GET['videoId']) ? $_GET['videoId'] : null;
if ($videoId == null) {
    throw new Exception('no videoId provided');
}

$path = null;
$video = Video::GetVideo($videoId);
$video->fetchMetadataIfMissing();
//force the video to load metadata from the FS
$video->loadMetadata(true);
//write the video to the database
$video->writeToDb();

//if this is a tv show, write all of its children
if ($video->mediaType === Enumerations::MediaType_TvShow) {
    $video->loadTvEpisodesFromFilesystem();
    $episodes = $video->getEpisodes();
    foreach ($episodes as $episode) {
        $episode->fetchMetadataIfMissing();
        $episode->loadMetadata(true);
        $episode->writeToDb();
    }
}

$result = (object) ['success' => true];
echo json_encode($result);
