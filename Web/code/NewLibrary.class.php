<?php

include_once('database/Queries.class.php');
include_once('VideoSource.class.php');
include_once(dirname(__FILE__) . '/Video/FileSystemVideo/FileSystemVideo.php');
include_once(dirname(__FILE__) . '/Video/FileSystemVideo/FileSystemMovie.php');
include_once(dirname(__FILE__) . '/Video/DbVideo/DbVideo.php');
include_once(dirname(__FILE__) . '/Video/DbVideo/DbMovie.php');

class NewLibrary {

    /**
     * Scans all source folders for media files and synchronizes the database with the watch folders 
     * @return boolean
     */
    function generateLibrary() {

        //get list of movie sources
        $movieSources = VideoSource::GetByType(Enumerations\MediaType::Movie);

        //get list of movies from db
        $moviesInDbQueryResults = \orm\Video::find('all');
        //create a DbVideo class for each movie from db
        $moviesInDb = [];
        foreach ($moviesInDbQueryResults as $movieInDb) {
            $movie = new DbMovie($movieInDb->videoId);
            $movie->setVideoRecord($movieInDb);
            $moviesInDb[] = $movie;
        }
        //get list of movies from sources
        $moviesInFs = array();
        //search through each video source
        foreach ($movieSources as $source) {
            //get a list of each video in this movies source folder
            $listOfAllFilesInSource = getVideosFromDir($source->location);
            foreach ($listOfAllFilesInSource as $fullPathToFile) {
                //create a new Movie object
                $video = new FileSystemMovie($fullPathToFile, $source->location, $source->baseUrl);
                $moviesInFs[] = $video;
            }
        }

        //Get list of deleted movies (found in db but NOT found in filesystem)
        $moviesInDbButDeletedFromFs = array();
        foreach ($moviesInDb as $dbKey => $movieInDb) {
            $videoHasBeenFound = false;
            foreach ($moviesInFs as $fsKey => $movieInFs) {
                //if this item is null, then it has already been removed. move to the next item.
                if ($movieInFs === null) {
                    continue;
                }
                if ($movieInDb->path() === $movieInFs->getPath()) {
                    $videoHasBeenFound = true;
                    //remove the video from the filesystem list
                    $moviesInFs[$fsKey] = null;
                    break;
                }
            }

            //the video was not found. it has been deleted. put it into the delete list
            if ($videoHasBeenFound === false) {
                //add to list of movies to delete
                $moviesInDbButDeletedFromFs[] = $movieInDb;
                //remove from list of movies from db
                $moviesInDb[$dbKey] = null;
            }
        }
        //movies that WERE in the db, and ARE in the file system
        $existingMovies = array_filter($moviesInDb);

        //the remaining videos in $moviesInFs are new movies
        $newMovies = array_filter($moviesInFs);

        //write new movies to the db
        foreach ($newMovies as $newMovie) {
            //this process includes looking for an nfo file. If it finds one, use that info. Otherwise, look to the net
            $newMovie->loadMetadata();
            //save the new movie to the database
            $newMovie->save();

            //copy the new movie's poster to the public poster folder
            $newMovie->copyPosters();
        }

        //delete the movies from the db that were removed from the filesystem
        /* @var  $movieToDeleteFromDb \orm\Video */
        foreach ($moviesInDbButDeletedFromFs as $dbVideo) {
            DbVideo::Delete($dbVideo->getVideoId());
            $fsVideo = new FilesystemMovie($dbVideo->sourceUrl(), $dbVideo->sourcePath(), $dbVideo->path());
            $fsVideo->delete();
        }

        /* @var  $existingMovie \orm\Video */
        //do a series of checks on the existing videos to see if anything has changes
        foreach ($existingMovies as $dbVideo) {
            //if this video was loaded from an nfo file
            if ($dbVideo->metadataLoadedFromNfo() === true) {
                //if the poster is newer in the fs than from the db, 
                if ($existingMovie->posterLastModifiedDate() !== null) {
                    //re-copy the posters
                    $fsVideo = new FilesystemMovie($dbVideo->sourceUrl(), $dbVideo->sourcePath(), $dbVideo->path());
                    $fsVideo->generatePosters();
                    $fsVideo->copyPosters();
                }
            }
        }
    }

}

?>
