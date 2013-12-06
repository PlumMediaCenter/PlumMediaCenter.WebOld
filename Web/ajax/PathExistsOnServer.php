<?php

$path = isset($_GET["path"]) ? $_GET["path"] : false;
$pathExists = file_exists($path);
echo json_encode($pathExists);
?>
