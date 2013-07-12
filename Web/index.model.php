<?php

class indexModel extends Model {

    public $videoCount;
    public $movieCount;
    public $tvShowCount;

    function __construct() {
        $lib = getLibrary();
        $this->videoCount = count($lib->movies) + count($lib->tvShows);
        $this->movieCount = count($lib->movies);
        $this->tvShowCount = count($lib->tvShows);
    }

}

?>
