<?php

include("code/Page.class.php");
global $title;
$p = new Page(__FILE__);
$m = $p->getModel();
$m->title = "Roku LAN Video Player";
$p->show();
?>