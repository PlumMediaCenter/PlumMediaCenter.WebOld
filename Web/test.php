<?php

include_once("code/Library.class.php");
$lib = new Library();
$lib->loadFromDatabase();
$lib->prepareVideosForJsonification();
echo json_encode($lib, JSON_PRETTY_PRINT);
?>
