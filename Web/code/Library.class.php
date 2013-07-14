<?php

include_once("Video.class.php");
include_once("TvShow.class.php");
include_once("TvEpisode.class.php");
include_once("Movie.class.php");

class Library {

    public $movies = [];
    public $tvShows = [];
    //this list holds each video as a duplicate, for easier full library searching
    private $videos = [];

    function __construct() {
        
    }

    function loadShallowFromJson() {
        //load the json file into memory
        $json = $string = file_get_contents(dirname(__FILE__) . "/../videos.json");
        $lib = json_decode($json);
        return $lib;
    }

    function loadFullFromJson() {
        $lib = $this->loadShallowFromJson();
        //spin through the movies, load an actual movie object from each
        foreach ($lib->movies as $movie) {
            $v = new Movie($movie->baseUrl, $movie->basePath, $movie->fullPath);
            $this->movies[] = $v;
            $this->videos[] = $v;
        }
        //spin through the movies, load an actual movie object from each
        foreach ($lib->tvShows as $tvShow) {
            $show = new TvShow($tvShow->baseUrl, $tvShow->basePath, $tvShow->fullPath);
            $show->setSeasons($tvShow->seasons);
            $this->tvShows[] = $show;
            $this->videos[] = $show;
            //merge the list of videos with this new list of tv shows
            $this->videos = array_merge($this->videos, $show->getEpisodes());
        }
    }

    function getVideo($fullPath) {
        foreach ($this->videos as $key => $v) {
            if ($v->fullPath == $fullPath) {
                return $v;
            }
        }
        return null;
    }

    public function update($fullPath) {
        $v = $this->getVideo($fullPath);
        $v->update();
    }

    /**
     * Write any updates done to the library to the json file
     */
    public function flush() {
        $videoList = json_encode($this, JSON_PRETTY_PRINT);
        file_put_contents(dirname(__FILE__) . "/../videos.json", $videoList);
    }

    function videoIndexof($fullPath) {
        foreach ($this->videos as $key => $v) {
            if ($v->fullPath == $fullPath) {
                return $key;
            }
        }
        return null;
    }

}

?>
