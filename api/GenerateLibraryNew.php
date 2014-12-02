<?php

//allow up to half hour for this script to run
set_time_limit(1800);
$result = (object) [];
require_once(dirname(__FILE__) . '/../code/LibraryGenerator.class.php');

$l = new LibraryGenerator();
$success = $l->generateLibrary();

echo json_encode($result);
?>
