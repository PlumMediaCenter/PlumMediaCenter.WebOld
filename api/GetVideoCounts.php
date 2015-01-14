<?php
include_once(dirname(__FILE__) . "/../code/Library.class.php");
$counts = Library::GetVideoCounts();

header('Content-Type: application/json');
echo json_encode($counts, JSON_PRETTY_PRINT);
?>