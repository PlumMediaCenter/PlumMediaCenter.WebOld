<?php

include("code/Page.class.php");
include_once(dirname(__FILE__) . "/code/Library.class.php");
$p = new Page(__FILE__);
$m = $p->getModel();
writeToLog("Beginning library update");
$l = new Library();
//load the library from the filesystem
$l->loadFromFilesystem();
$l->logLibraryStatus();

//write the library to the database
$m->updateSuccess = $l->writeToDb();

$l->writeLibraryJson();

writeToLog("Library update complete");
$p->setModel($m);
$p->show();
?>
