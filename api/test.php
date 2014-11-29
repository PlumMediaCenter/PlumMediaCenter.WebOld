<?php
include_once("../code/Video.class.php");
//use this to test random functionality
$v = Video::GetVideo(17);
echo json_encode($v->metadataInDatabaseIsUpToDate());
?>
