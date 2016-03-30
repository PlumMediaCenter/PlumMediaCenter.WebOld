<?php

//allow up to half hour for this script to run
set_time_limit(1800);
$result = (object) [];
require_once(dirname(__FILE__) . '/../code/LibraryGeneratorNew.php');

$l = new LibraryGeneratorNew();
$result = $l->generateLibrary();

echo json_encode($result);
?>
