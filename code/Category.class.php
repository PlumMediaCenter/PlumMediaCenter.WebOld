<?php

class Category {

    public $videos = [];
    public $count = 0;
    public $name;

    function __construct($name, $videos) {
        $this->name = $name;
        $this->videos = $videos;
        $this->count = count($this->videos);
    }

}
