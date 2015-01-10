<?php
//if($_SERVER['REQUEST_METHOD'] !== 'DELETE'){
//    throw new Exception('Unknown method');
//}
include_once(dirname(__FILE__) . "/../code/database/Queries.class.php");
$data = (array)json_decode(file_get_contents('php://input'));

$id = isset($data["id"]) ? $data["id"] : "";
echo json_encode(Queries::DeleteVideoSource($id));
?>
