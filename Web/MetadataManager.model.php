<?php

include_once("code/functions.php");
include_once("code/LibraryGenerator.class.php");
include_once("code/Enumerations.class.php");

class MetadataManagerModel {

    public $movies;
    public $tvShows;
    public $selectedTab = Enumerations::MediaType_Movie;

    public function __construct() {
        $l = new LibraryGenerator();
        $l->loadFromDatabase();
        $this->movies = $l->getMovies();
        $this->tvShows = $l->getTvShows();
        //get the selected tab
        if (isset($_GET["mediaType"])) {
            $t = $_GET["mediaType"];
            //if the selected tab is valid, save it
            if ($t === Enumerations::MediaType_Movie || $t == Enumerations::MediaType_TvShow || $t === Enumerations::MediaType_TvEpisode) {
                $this->selectedTab = $t;
            }
        }
    }

}

?>
