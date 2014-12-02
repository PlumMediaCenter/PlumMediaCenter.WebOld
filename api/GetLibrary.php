<?php
include_once(dirname(__FILE__) . '/../code/functions.php');
$videos = getLibrary();
header('Content-Type: application/json');

echo json_encode($videos);
?>
