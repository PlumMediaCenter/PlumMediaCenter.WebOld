<?php

require_once(dirname(__FILE__) . '/../code/Library.class.php');

$videoSourceId = isset($_GET['videoSourceId']) ? $_GET['videoSourceId'] : null;
$path = isset($_GET['path']) ? $_GET['path'] : null;
$newVideoIds = Library::AddNewMediaItem($videoSourceId, $path);
$result = (object) ['success' => true, 'newVideoIds' => $newVideoIds];
echo json_encode($result);
?>
