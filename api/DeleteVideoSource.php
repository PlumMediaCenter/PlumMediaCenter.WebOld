<?php
if($_SERVER['REQUEST_METHOD'] !== 'DELETE'){
    throw new Exception('Unknown method');
}
include_once(dirname(__FILE__) . "/../code/database/Queries.class.php");
include_once(dirname(__FILE__) . "/../code/Library.class.php");

$data = (array)json_decode(file_get_contents('php://input'));

$id = isset($data["id"]) ? $data["id"] : "";

//delete the source
$result = Queries::DeleteVideoSource($id);

//clear the cache
Library::ClearCache();

echo json_encode($result);
?>
