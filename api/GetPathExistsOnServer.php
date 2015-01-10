<?php
$path = isset($_GET["path"]) ? $_GET["path"] : false;
$result = [
    'exists' => file_exists($path)
];
echo json_encode($result);
?>
