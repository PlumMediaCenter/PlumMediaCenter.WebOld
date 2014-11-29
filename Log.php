<?php

include_once("code/Page.class.php");
$p = new Page(__FILE__);
$m = $p->getModel();
if(isset($_POST["clearLog"])){
    clearLog();
}
if (file_exists("log.txt")) {
    //put the log lines in reverse order to see most recent items first
    $m->logLines = array_reverse(file("log.txt"));
} else {
    $m->logLines = [];
}
$p->show();
?>