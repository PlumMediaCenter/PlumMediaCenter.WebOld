<?php

$result = (object) [];

require_once(dirname(__FILE__) . '/../code/Library.class.php');

$l = new Library();
$result->successLoadingFromFilesystem = $l->loadFromFilesystem();
$result->successWritingToDb = $l->writeToDb();
$result->successWritingLibraryJson = $l->writeLibraryJson();
$result->success = $result->successLoadingFromFilesystem && $result->successWritingToDb && $result->successWritingLibraryJson;
echo json_encode($result);
?>
