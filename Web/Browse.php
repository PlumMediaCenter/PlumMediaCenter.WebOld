<?php

include("code/Page.class.php");
include("code/Enumerations.class.php");


$p = new Page(__FILE__);
$m = $p->getModel();
$lib = getLibrary();
$m->movies = $lib->movies;
$m->tvShows = $lib->tvShows;
$m->mediaType = isset($_GET["mediaType"]) ? $_GET["mediaType"] : null;
$p->show();
?>