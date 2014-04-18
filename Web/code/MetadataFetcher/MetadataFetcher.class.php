<?php

include_once(dirname(__FILE__) . '/../Interfaces/iVideoMetadata.php');

abstract class MetadataFetcher implements iVideoMetadata {

    abstract function searchByTitle($title, $year);

    abstract function searchById($id);

    protected $fetchSuccess = false;

    function getFetchSuccess() {
        return $this->fetchSuccess;
    }

}

?>
