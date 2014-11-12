<?php

require_once(dirname(__FILE__) . '/../code/database/CreateDatabase.class.php');
$serverVersionNumber = CreateDatabase::CurrentDbVersion();
echo json_encode($serverVersionNumber);
?>
