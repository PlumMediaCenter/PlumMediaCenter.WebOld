<?php

include_once(dirname(__FILE__) . '/../Interfaces/iVideo.php');

abstract class MetadataFetcher implements iVideo{

    abstract function searchByTitle($title, $year);

    abstract function searchById($id);

    protected $fetchSuccess = false;

    function getFetchSuccess() {
        return $this->fetchSuccess;
    }

}

?>
