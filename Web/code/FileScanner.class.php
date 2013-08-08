<?php

/**
 * Returns an array of all movies found
 * @param type $baseUrl - the base url to be associated with this collection of videos
 * @param type $basePath -- the base path to start looking for videos at 
 * @return \Movie
 */
function getMovies($baseUrl, $basePath) {
    $movieList = [];
    //get a list of each video in the movies folder
    //get a list of every file in the current video source directory
    $listOfAllFilesInSource = getVideosFromDir($basePath);

    //spin through every folder in the source location
    foreach ($listOfAllFilesInSource as $fullPathToFile) {
        //create a new Movie object
        writeToLog("New Movie: $fullPathToFile");
        $video = new Movie($baseUrl, $basePath, $fullPathToFile);
        $video->writeToDb();
        //add the movie object to the list of all movies
        for ($i = 0; $i < dupNum(); $i++) {
            $movieList[] = $video;
        }
        writeToLog("Finished $fullPathToFile");
    }
    return $movieList;
}

/**
 * Returns an array of all tv shows found
 * @param type $baseUrl - the base url to be associated with this collection of videos
 * @param type $basePath -- the base path to start looking for tv shows at
 * @return \TvShow
 */
function getTvShows($baseUrl, $basePath) {
    $tvShowsList = [];
    //get a list of each video in the movies folder
    //get a list of every file in the current video source directory
    $listOfAllFilesInSource = getFoldersFromDirectory($basePath);

    //spin through every folder in the source location
    foreach ($listOfAllFilesInSource as $fullPathToFile) {
        //if the current file is a video file that we can add to our library
        //create a new Movie object
        $video = new TvShow($baseUrl, $basePath, $fullPathToFile);

        //tell the tv show to scan subdirectories for tv episodes
        $video->getTvEpisodes();

        //if this tv show has at least one season (which means it has at least one episode), then add it to the list
        if (count($video->seasons) > 0) {
            $tvShowsList[] = $video;
            $video->writeToDb();
            foreach ($video->seasons as $season) {
                foreach ($season as $episode) {
                    $episode->writeToDb();
                }
            }
        }
    }
    return $tvShowsList;
}

?>
