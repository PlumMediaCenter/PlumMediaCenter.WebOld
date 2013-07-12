<?php

include("code/Page.class.php");

$p = new Page(__FILE__);
$m = $p->getModel();
$lib = getLibrary();
$m->movies = $lib->movies;
$m->tvShows = $lib->tvShows;
$p->show();
?>