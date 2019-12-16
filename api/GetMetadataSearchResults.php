<?php

include_once(dirname(__FILE__) . "/../code/Video.class.php");
include_once(dirname(__FILE__) . "/../code/Enumerations.class.php");
include_once(dirname(__FILE__) . "/../config.php");

class Metadata
{
    public function __construct($mediaType, $metadataFetcher)
    {
        $this->mediaType = $mediaType;
        $this->title = $metadataFetcher->title();
        $this->plot = $metadataFetcher->plot();
        $this->mpaa = $metadataFetcher->mpaa();
        $this->posterUrl = $metadataFetcher->posterUrl();
        $this->tmdbId = intval($metadataFetcher->tmdbId());
        $this->year = $metadataFetcher->year() !== null ? $metadataFetcher->year() : null;
    }
}

$mediaType = isset($_GET['mediaType']) ? $_GET['mediaType'] : null;
$tmdbId = isset($_GET['tmdbId']) ? intval($_GET['tmdbId']) : null;
$title = isset($_GET['title']) ? $_GET['title'] : null;
$results = [];

//load the video

$metadataFetcherClass = Video::GetVideoMetadataFetcherClass($mediaType);
if ($tmdbId !== null) {
    $metadataFetcherClass->searchById($tmdbId);
    $results[] = new Metadata($mediaType, $metadataFetcherClass);
} else if ($title !== null) {
    $fetchers = $metadataFetcherClass->getFetchersByTitle($title);
    $fetchersCount = count($fetchers);
    for ($i = 0; $i < $fetchersCount; $i++) {
        $fetcher = $fetchers[$i];
        $results[] = new Metadata($mediaType, $fetcher);
    }
}

//get all of the search results for this video

header('Content-Type: application/json');
echo json_encode($results);
