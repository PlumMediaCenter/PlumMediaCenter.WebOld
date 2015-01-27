<?php

require_once(dirname(__FILE__) . '/../code/database/CreateDatabase.class.php');
$serverVersionNumber = CreateDatabase::CurrentDbVersion();
header('Content-Type: application/json');
echo json_encode($serverVersionNumber);
?>
