<?php

abstract class MetadataFetcher {

    abstract function searchByTitle($movieTitle);

    abstract function searchById($id);

    abstract function title();

    abstract function rating();

    abstract function plot();

    abstract function mpaa();
    
    abstract function posterUrl();
}

?>
