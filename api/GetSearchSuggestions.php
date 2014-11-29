<?php
require_once(dirname(__FILE__) . '/../code/Video.class.php');

$title = isset($_GET["title"]) ? $_GET["title"] : '';
$videos = Video::getSearchSuggestions($title);
header('Content-Type: application/json');   
echo json_encode($videos);

?>
