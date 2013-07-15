<?php

include_once("code/Page.class.php");
include_once("code/Video.class.php");
include_once("code/Movie.class.php");
include_once("code/TvShow.class.php");
include_once("code/TvEpisode.class.php");
include_once("code/Enumerations.class.php");


global $title;
$p = new Page(__FILE__);
$m = $p->getModel();
$m->title = "Manage Metadata and Posters";
$p->show();
?>