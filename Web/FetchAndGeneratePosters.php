<?php

include_once("code/Page.class.php");
include_once("code/LibraryGenerator.class.php");

$output = "";
if (isset($_GET["fetchAndGenerate"])) {
    $l = new LibraryGenerator();
    $results = $l->generateAndFetchPosters();
    $output = $results[0] . " Fetched. " . $results[1] . " Generated<br/><br/><a href='Log.php'>View Log</a>";
}
$p = new Page(__FILE__);
$m = $p->getModel();
$p->show('layout.php', $output);
?>