<?php

include_once("code/Page.class.php");
include_once("code/Enumerations.class.php");

$p = new Page(__FILE__);
$m = $p->getModel();
$p->show();
?>