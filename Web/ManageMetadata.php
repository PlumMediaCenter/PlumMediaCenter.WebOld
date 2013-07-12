<?php

include("code/Page.class.php");
global $title;
$p = new Page(__FILE__);
$m = $p->getModel();
$l = getLibrary();
$m->movies = $l->movies;
$m->tvShows = $l->tvShows;
$m->title = "Manage Metadata and Posters";
$p->show();
?>