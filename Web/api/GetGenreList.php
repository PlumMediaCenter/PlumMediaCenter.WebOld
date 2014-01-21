<?php

include_once(dirname(__FILE__) . "/../code/DbManager.class.php");

$genreNames = DbManager::singleColumnQuery("select genre_name from genre", "genre_name");
echo json_encode($genreNames);
?>
