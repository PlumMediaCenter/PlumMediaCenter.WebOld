<?php

include_once("code/functions.php");
include_once("code/Library.class.php");
include_once("code/Enumerations.class.php");

class MetadataManagerModel {

    public $movies;
    public $tvShows;
    public $tvEpisodes;
    public $selectedTab = Enumerations::MediaType_Movie;
    public $moviesLoaded = false;
    public $tvShowsLoaded = false;
    public $tvEpisodesLoaded = false;

    public function __construct() {
        $l = new Library();
        $this->movies = [];
        $this->tvShows = [];
        $this->tvEpisodes = [];

        //if a media type is specified, only load that media type
        if (isset($_GET["mediaType"]) &&
                ($_GET["mediaType"] === Enumerations::MediaType_Movie ||
                $_GET["mediaType"] === Enumerations::MediaType_TvShow ||
                $_GET["mediaType"] === Enumerations::MediaType_TvEpisode)
        ) {
            $this->selectedTab = $_GET["mediaType"];
            switch ($_GET["mediaType"]) {
                case Enumerations::MediaType_Movie:
                    $this->movies = $l->loadMoviesFromDatabase();
                    $this->moviesLoaded = true;
                    break;
                default:
                    $this->tvShows = $l->loadTvShowsFromDatabase();
                    $this->tvEpisodes = $l->tvEpisodes;
                    $this->tvShowsLoaded = true;
                    $this->tvEpisodesLoaded = true;
                    break;
            }
        } else {
            $l->loadFromDatabase();
            $this->movies = $l->movies;
            $this->tvShows = $l->tvShows;
            $this->tvEpisodes =  $l->tvEpisodes;
            $this->moviesLoaded = true;
            $this->tvShowsLoaded = true;
            $this->tvEpisodesLoaded = true;
        }
    }

}

?>
