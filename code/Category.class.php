<?php

class Category
{

    public $videoIds = [];
    public $name;
    public $title;

    function __construct($name, $videoIds, $title = null)
    {
        $this->name = $name;
        $this->title = $title ? $title : $name;
        $this->videoIds = $videoIds;
    }
}
