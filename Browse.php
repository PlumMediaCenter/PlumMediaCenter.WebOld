<?php

include_once("code/Page.class.php");
include_once("code/Enumerations.class.php");


$p = new Page(__FILE__);
$m = $p->getModel();
$lib = getLibrary();
$m->videos = getSortedVideos($lib);
$m->mediaType = isset($_GET["mediaType"]) ? $_GET["mediaType"] : null;
$p->show();

function getSortedVideos($lib) {
    //sort the movies and tv shows
    usort($lib, 'cmp');
    return $lib;
}

function cmp($a, $b) {
    if (isset($a) && isset($b) && isset($a->title) && isset($b->title)) {
        return strcmp($a->title, $b->title);
    } else {
        return true;
    }
}

?>