<?php

include("code/Page.class.php");

$p = new Page(__FILE__);
$m = $p->getModel();
//$m->videoUrl = isset($_GET["videoUrl"]) ? $_GET["videoUrl"] : -1;
//$m->posterUrl = isset($_GET["posterUrl"]) ? $_GET["posterUrl"] : -1;
$videoId = isset($_GET["videoId"]) ? $_GET["videoId"] : -1;
$m->init($videoId);
$p->show();
?>