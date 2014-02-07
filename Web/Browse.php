<?php

$dirname = dirname(__FILE__);
include_once("$dirname/code/Page.class.php");
include_once("$dirname/code/Library.class.php");
include_once("$dirname/code/Enumerations.class.php");


$p = new Page(__FILE__);
$m = $p->getModel();
$lib = new Library();
$lib->loadFromDatabase();
$m->videos = $lib->moviesAndTvShows;
$p->show();
?>
