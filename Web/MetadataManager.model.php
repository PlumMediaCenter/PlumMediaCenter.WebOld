<?php

include_once("code/Library.class.php");

class MetadataManagerModel {

    public $movies;
    public $tvShows;
    public $selectedTab = Enumerations::MediaType_Movie;

    public function __construct() {
        $l = Library::getJson();
        $this->movies = $l->movies;
        $this->tvShows = $l->tvShows;
        //get the selected tab

        if (isset($_GET["tab"])) {
            $t = $_GET["tab"];
            //if the selected tab is valid, save it
            if ($t === Enumerations::MediaType_Movie || $t == Enumerations::MediaType_TvShow || $t === Enumerations::MediaType_TvEpisode) {
                $this->selectedTab = $t;
            }
        }
    }

}

?>
