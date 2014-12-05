<?php

include_once("code/Page.class.php");
include_once("controllers/VideoController.php");

$p = new Page(__FILE__);

$model = (object)[];
$model->videos = [];

$p = new Page(__FILE__);
$searchString = isset($_GET["s"]) ? $_GET["s"] : '';
$model->searchString = $searchString;
if (isset($searchString)) {
    $model->videos = VideoController::SearchByTitle($searchString);
} else {
    
}
$p->setModel($model);
$p->show();
?>