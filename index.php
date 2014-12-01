<?php
include("code/Page.class.php");
$p = new Page(__FILE__);
$m = $p->getModel();
$p->show();
?>