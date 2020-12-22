<?php
include_once dirname(__FILE__) . "/../code/database/Queries.class.php";

$videoIds = isset($_GET["ids"]) ? json_decode($_GET["ids"]) : [];
if (count($videoIds) > 0) {
  //delete the source
  Queries::DeleteVideos($videoIds);
}
header("Content-Type: application/json");
echo "true";
