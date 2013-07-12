<?php

include("code/functions.php");

//get the search string
$s = $_GET["q"];

$lib = getLibrary();

//look for search results

$results = search($lib, $s);
if (isset($_GET["pretty"])) {
//print results to the client
    echo count($results) . " Results";

    echo "<pre>" . str_replace("\\", "", json_encode($results, JSON_PRETTY_PRINT)) . "</pre>";
} else {
    echo json_encode($results, JSON_PRETTY_PRINT);
}

function search($lib, $searchString) {
    $searchString = strtolower($searchString);
    $results = [];
    //spin through each movie
    foreach ($lib->movies as $key => $vid) {
        $t = strtolower($vid->title);
        //if the search string was found in the title of the video, add it to the results list
        if (strpos($t, $searchString) > -1) {
            $results[] = $vid;
        }
    }
    //spin through each tv show
    foreach ($lib->tvShows as $vid) {
        $t = strtolower($vid->title);
        //if the search string was found in the title of the video, add it to the results list
        if (strpos($t, $searchString) > -1) {
            $results[] = $vid;
        }
        //spin through each tv episode
        foreach ($vid->episodes as $ep) {
            $t = strtolower($ep->title);
            //if the search string was found in the title of the video, add it to the results list
            if (strpos($t, $searchString) > -1) {
                $results[] = $ep;
            }
        }
    }
    return $results;
}

?>
