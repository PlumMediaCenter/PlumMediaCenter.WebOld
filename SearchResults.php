<?php

include_once("code/Page.class.php");
include_once("code/Video.class.php");

$p = new Page(__FILE__);

$model = (object)[];
$model->videos = [];

$p = new Page(__FILE__);
$title = isset($_GET["title"]) ? $_GET["title"] : '';
if (isset($title)) {
    $model->videos = Video::searchByTitle($title);
} else {
    
}
$p->setModel($model);
$p->show();
?>