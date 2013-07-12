<?php

class Library {
    public $movies = [];
    function __construct() {
        
    }

    function loadShallowFromJson() {
        //load the json file into memory
        $json = $string = file_get_contents("videos.json");
        $lib = json_decode($json);
        return $lib;
    }

    function loadFullFromJson() {
        $lib = $this->loadShallowFromJson();
        //spin through the movies, load an actual movie object from each
        foreach($lib->movies as $movie){
            $this->movies[] = new Movie($movie->baseUrl, $movie->basePath, $movie->fullPath);
        }
    }

}

?>
