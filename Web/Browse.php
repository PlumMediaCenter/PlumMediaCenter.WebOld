<?php

$dirname = dirname(__FILE__);
include_once("$dirname/code/Page.class.php");
include_once("$dirname/code/Library.class.php");
include_once("$dirname/code/Enumerations.class.php");


$p = new Page(__FILE__);
$m = $p->getModel();
$lib = new Library();
$lib->loadFromDatabase();
//$lib = getLibrary();
$m->movies = $lib->movies;
$m->tvShows = $lib->tvShows;
$m->mediaType = isset($_GET["mediaType"]) ? $_GET["mediaType"] : null;
$p->show();
?>
