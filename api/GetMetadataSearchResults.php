<?php

include_once(dirname(__FILE__) . "/../code/Video.class.php");
include_once(dirname(__FILE__) . "/../code/Enumerations.class.php");

class Metadata {

    public function __construct($mediaType, $metadataFetcher) {
        $this->mediaType = $mediaType;
        $this->title = $metadataFetcher->title();
        $this->plot = $metadataFetcher->plot();
        $this->mpaa = $metadataFetcher->mpaa();
        $this->posterUrl = $metadataFetcher->posterUrl();
    }

}
header('Content-Type: application/json');

echo '[
    {
        "mediaType": 0,
        "title": "Dexter",
        "plot": "He\'s smart, he\'s good looking, and he\'s got a great sense of humor. He\'s Dexter Morgan, everyone\'s favorite serial killer. As a Miami forensics expert, he spends his days solving crimes, and nights committing them. But Dexter lives by a strict code of honor that is both his saving grace and lifelong burden. Torn between his deadly compulsion and his desire for true happiness, Dexter is a man in profound conflict with the world and himself.",
        "mpaa": "TV-MA",
        "posterUrl": "http:\/\/thetvdb.com\/banners\/posters\/79349-24.jpg"
    },
     {
        "mediaType": 0,
        "title": "Dexter",
        "plot": "He\'s smart, he\'s good looking, and he\'s got a great sense of humor. He\'s Dexter Morgan, everyone\'s favorite serial killer. As a Miami forensics expert, he spends his days solving crimes, and nights committing them. But Dexter lives by a strict code of honor that is both his saving grace and lifelong burden. Torn between his deadly compulsion and his desire for true happiness, Dexter is a man in profound conflict with the world and himself.",
        "mpaa": "TV-MA",
        "posterUrl": "http:\/\/thetvdb.com\/banners\/posters\/79349-24.jpg"
    },
     {
        "mediaType": 0,
        "title": "Dexter",
        "plot": "He\'s smart, he\'s good looking, and he\'s got a great sense of humor. He\'s Dexter Morgan, everyone\'s favorite serial killer. As a Miami forensics expert, he spends his days solving crimes, and nights committing them. But Dexter lives by a strict code of honor that is both his saving grace and lifelong burden. Torn between his deadly compulsion and his desire for true happiness, Dexter is a man in profound conflict with the world and himself.",
        "mpaa": "TV-MA",
        "posterUrl": "http:\/\/thetvdb.com\/banners\/posters\/79349-24.jpg"
    }
]';
return;
$mediaType = isset($_GET['mediaType']) ? intval($_GET['mediaType']) : null;
$onlineVideoId = isset($_GET['onlineVideoId']) ? $_GET['onlineVideoId'] : null;
$title = isset($_GET['title']) ? $_GET['title'] : null;
$results = [];

//load the video

$metadataFetcherClass = Video::GetVideoMetadataFetcherClass($mediaType);

if ($onlineVideoId !== null) {
    $metadataFetcherClass->searchById($onlineVideoId);
    $results[] = new Metadata($mediaType, $metadataFetcherClass);
} else if ($title !== null) {
    $results[] = $metadataFetcherClass->searchByTitle($title);
    $results[] = new Metadata($mediaType, $metadataFetcherClass);
}

//get all of the search results for this video

header('Content-Type: application/json');
echo json_encode($results);
?>