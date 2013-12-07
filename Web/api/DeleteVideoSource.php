<?php

include_once(dirname(__FILE__) . "/../code/database/Queries.class.php");

$sourcePath = isset($_GET["sourcePath"]) ? $_GET["sourcePath"] : "";
echo json_encode(Queries::DeleteVideoSource($sourcePath));
?>
