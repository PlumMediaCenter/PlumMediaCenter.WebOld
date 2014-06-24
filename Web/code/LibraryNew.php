<?php

include_once('database/Queries.class.php');
include_once('VideoSource.class.php');
include_once(dirname(__FILE__) . '/Video/FileSystemVideo/FileSystemVideo.php');
include_once(dirname(__FILE__) . '/Video/FileSystemVideo/FileSystemMovie.php');
include_once(dirname(__FILE__) . '/Video/DbVideo/DbVideo.php');
include_once(dirname(__FILE__) . '/Video/DbVideo/DbMovie.php');

class LibraryNew {

    /**
     * Returns a list of three sets of movies: new movies, deleted movies and existing movies.
     * @return {new: fileSystemVideo[], existing: fileSystemVideo[], deleted: fileSystemVideo[] }
     */
    public function getMovies() {

        //get list of movie sources
        $movieSources = VideoSource::GetByType(Enumerations\MediaType::Movie);

        //get list of movies from db
        $moviesInDbQueryResults = \orm\Video::find('all');
        //create a DbVideo class for each movie from db
        $moviesInDb = [];
        foreach ($moviesInDbQueryResults as $movieInDb) {
            $movie = new DbMovie($movieInDb->video_id);
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
                $video = new FileSystemMovie($fullPathToFile, $source->location, $source->base_url);
                $moviesInFs[] = $video;
            }
        }

        //Get list of deleted movies (found in db but NOT found in filesystem)
        $moviesInDbButDeletedFromFs = array();
        //look at each movie from db
        foreach ($moviesInDb as $dbKey => $movieInDb) {
            $videoHasBeenFound = false;
            $filesystemVideo = null;
            //look at each movie from filesystem
            foreach ($moviesInFs as $fsKey => $movieInFs) {
                //if this item is null, then it has already been removed. move to the next item.
                if ($movieInFs === null) {
                    continue;
                }
                if ($movieInDb->path() === $movieInFs->path()) {
                    $videoHasBeenFound = true;
                    //save this video object since we want to return a list of all of the filesystem videos
                    $filesystemVideo = $moviesInFs[$fsKey];
                    //remove the video from the filesystem list
                    $moviesInFs[$fsKey] = null;
                    break;
                }
            }

            //the video was not found. it has been deleted. put it into the delete list
            if ($videoHasBeenFound === false) {
                //add to list of movies to delete
                $moviesInDbButDeletedFromFs[] = $filesystemVideo;
                //remove from list of movies from db
                $moviesInDb[$dbKey] = null;
            }
        }
        //movies that WERE in the db, and ARE in the file system
        $existingDbMovies = array_filter($moviesInDb);
        $existingFsMovies = array();
        /* @var $dbMovie DbMovie */
        foreach ($existingDbMovies as $dbMovie) {
            $existingFsMovies[] = new FileSystemMovie($dbMovie->path(), $dbMovie->sourcePath(), $dbMovie->sourceUrl());
        }

        //the remaining videos in $moviesInFs are new movies
        $newMovies = array_filter($moviesInFs);

        //collect the lists into a single object
        $result = (object) array();
        $result->new = $newMovies;
        $result->existing = $existingFsMovies;
        $result->deleted = $moviesInDbButDeletedFromFs;

        return $result;
    }

    /**
     * Scans all source folders for media files and synchronizes the database with the watch folders 
     * @return boolean
     */
    function generateLibrary() {
        $movies = $this->getMovies();

        //write new movies to the db
        foreach ($movies->new as $newVideo) {
            //this process includes looking for an nfo file. If it finds one, use that info. Otherwise, look to the net
            $newVideo->loadMetadata();
            //save the new movie to the database (and also copies its posters)
            $newVideo->save();
        }

        //delete the movies from the db that were removed from the filesystem
        foreach ($movies->deleted as $deletedVideo) {
            $deletedVideo->delete();
        }

        /* @var  $existingMovie \orm\Video */
        //do a series of checks on the existing videos to see if anything has changed
        foreach ($movies->existing as $existingVideo) {
            $existingVideo->saveIfChanged();
        }
    }

}

?>
