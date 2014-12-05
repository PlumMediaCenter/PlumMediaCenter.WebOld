<?php
include_once("../code/Database/CreateDatabase.class.php");
$c = new CreateDatabase(config::$dbUsername, config::$dbPassword, config::$dbHost);
$c->db0_2_0();
?>
