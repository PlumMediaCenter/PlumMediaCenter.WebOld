<?php

include("code/Page.class.php");
include_once(dirname(__FILE__) . "/code/LibraryGenerator.class.php");
$p = new Page(__FILE__);
$m = $p->getModel();
$lg = new LibraryGenerator();
  $movieSources = Queries::getVideoSources(Enumerations::MediaType_Movie);
$m->updateSuccess = $lg->updateLibrary();
$p->setModel($m);
$p->show();
?>
