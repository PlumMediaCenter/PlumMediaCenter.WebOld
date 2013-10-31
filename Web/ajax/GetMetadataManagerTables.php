<?php

include_once(dirname(__FILE__) . "/../Code/Enumerations.class.php");
include_once(dirname(__FILE__) . "/../Code/Library.class.php");

$result = [];
$result[Enumerations::MediaType_Movie] = [];
$result[Enumerations::MediaType_TvShow] = [];
$result[Enumerations::MediaType_TvEpisode] = [];

if (isset($_GET["mediaType"])) {
    $l = new Library();
    switch ($_GET["mediaType"]) {
        case Enumerations::MediaType_Movie:
            $l->loadMoviesFromDatabase();
            ob_start();
            printVideoTable($l->movies);
            $result[Enumerations::MediaType_Movie] = ob_get_contents();
            ob_end_clean();
            ob_start();
            break;
        case Enumerations::MediaType_TvShow:
        case Enumerations::MediaType_TvEpisode:
            $l->loadTvShowsFromDatabase();
            ob_start();
            printVideoTable($l->tvShows);
            $result[Enumerations::MediaType_TvShow] = ob_get_contents();
            ob_end_clean();
            ob_start();
            printVideoTable($l->tvEpisodes);
            $result[Enumerations::MediaType_TvEpisode] = ob_get_contents();
            ob_end_clean();
            break;
    }
}

echo json_encode($result);
?>
