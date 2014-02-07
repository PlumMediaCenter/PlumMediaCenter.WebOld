<?php

$dirname = dirname(__FILE__);
include_once("$dirname/code/Page.class.php");
include_once("$dirname/code/Library.class.php");
include_once("$dirname/code/Enumerations.class.php");

$searchString = (isset($_GET["q"])) ? $_GET["q"] : "";
$p = new Page(__FILE__);
$m = $p->getModel();
$m->videos = Library::SearchByTitle($searchString);
$p->show();
?>
