<?php
echo "0.2.0";
return;
require_once(dirname(__FILE__) . '/../code/database/CreateDatabase.class.php');
$serverVersionNumber = CreateDatabase::CurrentDbVersion();
echo json_encode($serverVersionNumber);
?>
