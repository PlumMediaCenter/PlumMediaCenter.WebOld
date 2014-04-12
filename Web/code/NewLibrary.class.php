<?php

include_once("database/Queries.class.php");
include_once("VideoSource.class.php");
include_once(dirname(__FILE__) . "/FileSystemVideo/FileSystemVideo.class.php");
include_once(dirname(__FILE__) . "/FileSystemVideo/FileSystemMovie.class.php");

class NewLibrary {

    /**
     * Scans all source folders for media files and synchronizes the database with the watch folders 
     * @return boolean
     */
    function generateLibrary() {
        //get list of movie sources
        $movieSources = VideoSource::GetByType(Enumerations\MediaType::Movie);

        //get list of movies from db
        $moviesInDb = \orm\Video::find('all', array('select' => 'path'));

        //get list of movies from sources
        $moviesInFs = array();
        //search through each video source
        foreach ($movieSources as $source) {
            //get a list of each video in this movies source folder
            $listOfAllFilesInSource = getVideosFromDir($source->location);
            foreach ($listOfAllFilesInSource as $fullPathToFile) {
                //create a new Movie object
                $video = new FileSystemMovie($fullPathToFile, $source->location, $source->base_url);
                $moviesInFs[] = $video;
            }
        }

        //Get list of deleted movies (found in db but NOT found in filesystem)
        $deletedMoviesFromDb = array();
        $existingMovies = array_filter($moviesInDb);
        foreach ($moviesInDb as $dbKey => $movieInDb) {
            $videoHasBeenFound = false;
            foreach ($moviesInFs as $fsKey => $movieInFs) {
                //if this item is null, then it has already been removed. move to the next item.
                if ($movieInFs === null) {
                    continue;
                }
                if ($movieInDb->path === $movieInFs->path) {
                    $videoHasBeenFound = true;
                    //remove the video from the filesystem list
                    $moviesInFs[$fsKey] = null;
                    break;
                }
            }

            //the video was not found. it has been deleted. put it into the delete list
            if ($videoHasBeenFound === false) {
                //add to list of movies to delete
                $deletedMoviesFromDb[] = $movieInDb;
                //remove from list of movies from db
                $moviesInDb[$dbKey] = null;
            }
        }

        //the remaining videos in $moviesInFs are new movies
        $newMovies = array_filter($moviesInFs);
        
        //write new movies to the db
        foreach($newMovies as $newMovie){
            $newMovie->loadMetadata();
        }
        
        
        
    }

}

?>
