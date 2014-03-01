<?php

include_once("code/functions.php");
include_once("code/Library.class.php");
include_once("code/Enumerations.class.php");

class MetadataManagerModel {

    public $movies;
    public $tvShows;
    public $tvEpisodes;
    public $selectedTab = Enumerations\MediaType::Movie;
    public $moviesLoaded = false;
    public $tvShowsLoaded = false;
    public $tvEpisodesLoaded = false;

    public function __construct() {
        $l = new Library();
        $l->loadFromDatabase();
        $this->movies = $l->movies;
        $this->tvShows = $l->tvShows;
        $this->tvEpisodes = $l->tvEpisodes;
        $this->moviesLoaded = true;
        $this->tvShowsLoaded = true;
        $this->tvEpisodesLoaded = true;
    }

}

?>
