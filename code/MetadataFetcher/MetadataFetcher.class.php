<?php

abstract class MetadataFetcher {

    abstract function title();

    abstract function rating();

    abstract function plot();

    abstract function mpaa();

    abstract function tmdbId();

    abstract function posterUrl();

    abstract function searchByTitle($title);

    abstract function searchById($id);

    abstract function year();

    public $language;

    function setLanguage($language) {
        $this->language = $language;
    }

    protected $fetchSuccess = false;

    function getFetchSuccess() {
        return $this->fetchSuccess;
    }

}

?>
