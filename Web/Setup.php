<?php

$success = null;
//if the generateDatabase button was clicked, generate the database
if (isset($_POST["setup"])) {
    $success = false;
    $username = isset($_POST["mysqlRootUsername"]) ? $_POST["mysqlRootUsername"] : null;
    $password = isset($_POST["mysqlRootPassword"]) ? $_POST["mysqlRootPassword"] : "";
    $host = isset($_POST["mysqlHostName"]) ? $_POST["mysqlHostName"] : null;
    if ($username != null && $password !== null && $host != null) {
        include_once("code/database/CreateDatabase.class.php");
        $cd = new CreateDatabase($username, $password, $host);
        $success = $cd->createDatabase();
    }

    //delete any previously existing library.json file 
    file_put_contents(dirname(__FILE__) . "/api/library.json", "");
}
include_once("code/Page.class.php");
$p = new Page(__FILE__);
$m = $p->getModel();
$m->success = $success;
$p->show();
?>