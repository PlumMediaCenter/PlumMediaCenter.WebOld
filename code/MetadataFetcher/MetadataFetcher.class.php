<?php

abstract class MetadataFetcher {

    abstract function title();

    abstract function rating();

    abstract function plot();

    abstract function mpaa();

    abstract function onlineVideoId();

    abstract function posterUrl();

    abstract function searchByTitle($title);

    abstract function searchById($id);
    

    protected $fetchSuccess = false;

    function getFetchSuccess() {
        return $this->fetchSuccess;
    }

}

?>
