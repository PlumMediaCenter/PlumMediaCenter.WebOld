<?php

include("code/Page.class.php");
include_once(dirname(__FILE__) . "/code/LibraryGenerator.class.php");
$p = new Page(__FILE__);
$m = $p->getModel();
$lg = new LibraryGenerator();
if (isset($_GET["generate"])) {
    $m->updateSuccess = $lg->generateLibrary();
    $m->action = "Library Re-Generated";
} else {
    $m->action = "Library Updated";
    $m->updateSuccess = $lg->updateLibrary();
}
$p->setModel($m);
$p->show();
?>
