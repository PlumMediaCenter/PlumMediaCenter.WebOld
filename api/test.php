<?php

include_once("../code/database/CreateDatabase.class.php");
include_once(dirname(__FILE__) . '/../config.php');

$cd = new CreateDatabase('root','', config::$dbHost);
$cd->db0_3_0();
?>
