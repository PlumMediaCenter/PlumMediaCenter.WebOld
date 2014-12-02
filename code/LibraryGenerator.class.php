<?php

include_once('database/Queries.class.php');
include_once('Movie.class.php');

class LibraryGenerator {

    /**
     * Scans all source folders for media files and synchronizes the database with the watch folders 
     * @return boolean
     */
    function generateLibrary() {
        $pdo = DbManager::getPdo();
        $sql = "select video_id, path, poster_last_modified_date, metadata_last_modified_date" .
                "from video " .
                "where media_type = '" . Enumerations::MediaType_Movie . "'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $movies = DbManager::FetchAllClass($stmt);

        //delete any movies that are no longer on the file system
        $existingMovieData = $this->deleteMissingMovies($movies);
        //update any movies that have changed on the file system
        $this->updateExistingMovies($existingMovieData);
        //add any new movies
        $this->addNewMovies($existingMovieData);
    }

    /**
     * Delete all of the movies from the list of paths that are no longer on the filesystem
     */
    private function deleteMissingMovies($movies) {
        $deletedVideoIds = [];
        $nonDeletedMovies = [];

        foreach ($movies as $movie) {
            $path = $movie->path;
            $videoId = $movie->video_id;
            if (file_exists($path)) {
                $nonDeletedVideoDataItems[] = $movie;
            } else {
                $deletedVideoIds[] = $videoId;
            }
        }
        //delete all of the videos that are no longer present on the file system
        Queries::DeleteVideos($deletedVideoIds);
        //return the list of videos that were NOT deleted
        return $nonDeletedMovies;
    }

    private function updateExistingMovies($movies) {
        $posterModifiedMovies = [];
        $nfoModifiedMovies = [];
        foreach ($movies as $movie) {
            $path = $movie->path;
            $dbPosterLastModifiedDate = $movie->poster_last_modified_date;
            $fsPosterLastModifiedDate = Movie::GetMoviePosterLastModifiedDate($path);
            if ($dbPosterLastModifiedDate != $fsPosterLastModifiedDate && $fsPosterLastModifiedDate != null) {
                $posterModifiedMovies[] = $movie;
            }

            //if the movie's metadata has been updated
            $dbNfoLastModifiedDate = $movie->metadata_last_modified_date;
            $fsNfoLastModifiedDate = Movie::GetMovieNfoLastModifiedDate($path);
            if ($dbNfoLastModifiedDate != $fsNfoLastModifiedDate && $fsNfoLastModifiedDate != null) {
                $nfoModifiedMovies[] = $movie;
            }
        }

        //for each video that had an updated poster, regenerate the resized posters and save the urls to the db
        foreach ($posterModifiedMovies as $movie) {
            Movie::GenerateMoviePosters($movie->path);
            Movie::UpdateDbPosterDate($movie->video_id, $movie->path);
        }

        //for each video that had updated metadata, save that video
        foreach ($nfoModifiedMovies as $movie) {
            $updatedMovie = Movie::SaveMovieNfoToDb($movie->video_id, $movie->path);
        }
    }

    private function addNewMovies($existingMovies) {
        //create a hashtable of all of the paths that are ALREADY in our database
        $hash = [];
        foreach ($existingMovies as $movie) {
            $hash[$movie->path] = true;
        }
        //list of all video sources
        $movieSources = Queries::getVideoSources(Enumerations::MediaType_Movie);
        $movies = [];
        foreach ($movieSources as $source) {
            //get a list of each video in this movies source folder
            $listOfAllFilesInSource = getVideosFromDir($source->location);

            foreach ($listOfAllFilesInSource as $fullPathToFile) {
                //if we don't already have this one in the database, add it to the new list
                if (!isset($hash[$fullPathToFile])) {
                    $video = new Movie($source->base_url, $source->location, $fullPathToFile);
                }
            }
        }

        //write each of the new movies to the database
        foreach ($movies as $movie) {
            $movie->writeToDb();
        }
        return true;
    }

}

?>