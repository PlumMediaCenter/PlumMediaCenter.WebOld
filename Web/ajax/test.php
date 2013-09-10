<?php
include_once("../code/Video.class.php");
//use this to test random functionality
$v = Video::loadFromDb(17);
echo json_encode($v->metadataInDatabaseIsUpToDate());
?>
