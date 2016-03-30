<?php

require_once(dirname(__FILE__) . '/../code/LibraryGenerator.class.php');
$g = new LibraryGenerator();
echo $g->deleteMissingVideos();
?>
