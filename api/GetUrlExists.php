<?php

$url = isset($_GET["url"]) ? $_GET["url"] : false;
$headers = @get_headers($url);
if ($headers === false || $headers[0] == 'HTTP/1.1 404 Not Found') {
    $exists = false;
} else {
    $exists = true;
}
$result = [
    'exists' => $exists
];
header('Content-Type: application/json');
echo json_encode($result);
?>
