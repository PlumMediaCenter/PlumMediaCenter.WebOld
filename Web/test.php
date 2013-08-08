<?php

include_once("code/LibraryGenerator.class.php");
$libGen = new LibraryGenerator(Array("C:/Videos/Movies/"), Array("http://localhost:8080/localvideo/Movies"), Array("C:/Videos/Tv Shows/"), Array("http://localhost:8080/localvideo/Tv Shows/"));
$libGen->generateLibrary();
?>
