<?php

//allow up to half hour for this script to run
set_time_limit(1800);
$result = (object) [];
require_once(dirname(__FILE__) . '/../code/Library.class.php');

$l = new Library();
$result->successLoadingFromDatabase = $l->loadFromFilesystem();
try{
    $l->fetchMissingMetadataAndPosters();
    $l->loadFromFilesystem();
} catch(Exception $e){

}
$result->successWritingToDb = $l->writeToDb();
$result->successWritingLibraryJson = $l->writeLibraryJson();
$result->success = $result->successLoadingFromDatabase && $result->successWritingToDb && $result->successWritingLibraryJson;
echo json_encode($result);
?>
