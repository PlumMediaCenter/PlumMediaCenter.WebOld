<?php
include_once("code/Library.class.php");
class ManageMetadataModel {

    public $movies;
    public $tvShows;

    public function __construct() {
        $l = new Library();
        $l->loadFullFromJson();
        $this->movies = $l->movies;
        $this->tvShows = $l->tvShows;
    }
}

?>
