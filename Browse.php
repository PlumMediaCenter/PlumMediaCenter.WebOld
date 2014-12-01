<?php

include("code/Page.class.php");
include("code/Enumerations.class.php");


$p = new Page(__FILE__);
$m = $p->getModel();
$lib = getLibrary();
$m->movies = $lib->movies;
$m->tvShows = $lib->tvShows;
$m->videos = getSortedVideos($lib);
$m->mediaType = isset($_GET["mediaType"]) ? $_GET["mediaType"] : null;
$p->show();

function getSortedVideos($lib) {
    //add all of the videos to one array
    $all = [];
    foreach ($lib->movies as $video) {
        $all[] = $video;
    }
    foreach ($lib->tvShows as $video) {
        $all[] = $video;
    }

    //sort the movies and tv shows
    usort($all, 'cmp');
    return $all;
}

function cmp($a, $b) {
    if (isset($a) && isset($b) && isset($a->name) && isset($b->name)) {
        return strcmp($b->name, $a->name);
    } else {
        return true;
    }
}

?>