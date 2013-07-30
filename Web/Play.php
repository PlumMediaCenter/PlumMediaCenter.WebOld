<?php

include("code/Page.class.php");

$p = new Page(__FILE__);
$m = $p->getModel();
$m->videoUrl = $_GET["videoUrl"];
$m->posterUrl = $_GET["posterUrl"];
$p->show();
?>