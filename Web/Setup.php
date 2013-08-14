<?php

//if the generateDatabase button was clicked, generate the database
if (isset($_POST["createDatabase"])) {
    $username = isset($_POST["mysqlRootUsername"]) ? $_POST["mysqlRootUsername"] : null;
    $password = isset($_POST["mysqlRootPassword"]) ? $_POST["mysqlRootPassword"] : "";
    $host = isset($_POST["mysqlHostName"]) ? $_POST["mysqlHostName"] : null;
    if ($username != null && $password !== null && $host != null) {
        include_once("code/database/CreateDatabase.class.php");
        $cd = new CreateDatabase($username, $password, $host);
        $cd->createDatabase();
    }
}
include_once("code/Page.class.php");
$p = new Page(__FILE__);
$m = $p->getModel();
$p->show();
?>