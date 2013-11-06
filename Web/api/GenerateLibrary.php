<?php

$result = (object) [];

require_once(dirname(__FILE__) . '/../code/Library.class.php');

$l = new Library();
$result->successLoadingFromFilesystem = $l->loadFromFilesystem();
$result->successWritingToDb = $l->writeToDb();
$result->success = $result->successLoadingFromFilesystem && $result->successWritingToDb;
echo json_encode($result);
?>
