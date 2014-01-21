<?php

include_once("code/Library.class.php");
include_once("code/Bench/Bench.class.php");
$l = new Library();
$l->loadFromDatabase();
$l->sort();
?>
