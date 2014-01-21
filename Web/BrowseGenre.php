<?php

include("code/Page.class.php");
include("code/Enumerations.class.php");


$p = new Page(__FILE__);
$m = $p->getModel();
$p->show();
?>