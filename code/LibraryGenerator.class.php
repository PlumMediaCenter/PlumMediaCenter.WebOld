<?php

include_once('database/Queries.class.php');
include_once('controllers/VideoController.php');
include_once('Models/GenerateLibraryResultModel.php');

include_once('Movie.class.php');

class LibraryGenerator {

    const DELETE_PAGE_SIZE = 200;
    const ADD_PAGE_SIZE = 50;

    function ___deleteMissingVideos() {
        $deleteCount = 0;
        $idsToDelete = [];
        $pageSize = 200;
        //get the number of videos in the library
        $videoCount = intval(DbManager::SingleColumnQuery('select count(video_id) from video'));

        $iterations = ceil($videoCount / $pageSize);

        //grab the videos in chunks, make sure they exist
        for ($i = 0; $i < $iterations; $i++) {
            $offset = $i * $pageSize;
            $videos = DbManager::GetAllClassQuery("select video_id, path from video limit $offset,$pageSize");
            foreach ($videos as $video) {
                if (file_exists($video->path) === false) {
                    $idsToDelete[] = $video->video_id;
                }
            }
            //if we have videos to delete, delete them.
            if (!empty($idsToDelete)) {
                $success = DbManager::NonQuery('delete from tv_episode where video_id in(' . implode(',', $idsToDelete) . ')');
                $success = DbManager::NonQuery('delete from tv_show where video_id in(' . implode(',', $idsToDelete) . ')');
                $success = DbManager::NonQuery('delete from video where video_id in(' . implode(',', $idsToDelete) . ')');
                $deleteCount = $deleteCount + count($idsToDelete);
                unset($idsToDelete);
                $idsToDelete = [];
            }
        }
        return $deleteCount;
    }

    function ___addNewMovies() {
        $insertCount = 0;
        //list of all video sources
        $movieSources = Queries::getVideoSources(Enumerations::MediaType_Movie);

        $movies = [];
        foreach ($movieSources as $source) {
            //get a list of each video in this movies source folder
            $listOfAllFilesInSource = getVideosFromDir($source->location);

            foreach ($listOfAllFilesInSource as $fullPathToFile) {
                $movies[] = (object) [
                            'path' => $fullPathToFile,
                            'url' => '',
                            'filetype' => '',
                            'media_type' => '',
                            'video_source_path' => $source->location,
                            'video_source_url' => $source->base_url
                ];
                if (count($movies) === LibraryGenerator::ADD_PAGE_SIZE) {
                    $insertCount = $insertCount + count($movies);
                    $movies = [];
                }
            }
        }

        //write each of the new movies to the database
        foreach ($movies as $movie) {
            $movie->writeToDb();
        }
        return true;
    }

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
        VideoController::DeleteVideos($deletedVideoIds);
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

    private function addNewMovies_orig($existingMovies) {
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
                    $movie = new Movie($source->base_url, $source->location, $fullPathToFile);
                    $movies[] = $movie;
                }
            }
        }

        //write each of the new movies to the database
        foreach ($movies as $movie) {
            $movie->writeToDb();
        }
        return true;
    }

    /**
     * Add a new media item to the library
     * @param int $videoSourceId - if null, attempt to auto-detect it
     * @param string $path
     */
    public static function AddNewMediaItem($videoSourceId, $path) {
        $newVideoIds = [];
        $realpath = realpath($path);
        //get a video source somehow
        $videoSource = null;
        if ($videoSourceId === null) {
            //get all of the video sources
            $videoSources = Queries::GetVideoSources();
            foreach ($videoSources as $source) {
                if (strpos($realpath, realpath($source->location)) !== false) {
                    //this video source was found in the path    
                    if ($videoSource === null) {
                        $videoSource = $source;
                    } else {
                        throw new Exception('Cannot auto-detect new media item video source: multiple source matches were found');
                    }
                }
            }
            if ($videoSource === null) {
                throw new Exception('Cannot auto-detect new media item video source: no source matches were found');
            }
        } else {
            $videoSourceResults = Queries::GetVideoSourcesById([$videoSourceId]);
            if (count($videoSourceResults) === 1) {
                $videoSource = $videoSourceResults[0];
            } else {
                throw new Exception('Unable to find video source with that id');
            }
        }
        $pathIsFile = false;
        if (fileIsValidVideo($path)) {
            $pathIsFile = true;
        }

        if ($videoSource->media_type === Enumerations::MediaType_Movie) {
            $movies = [];
            $paths = [];
            if ($pathIsFile === true) {
                $paths = [$path];
            } else {
                //find all movies beneath this path
                $paths = getVideosFromDir($path);
            }

            foreach ($paths as $path) {
                if (strpos(strtolower($path), ".extra.") !== false) {
                    continue;
                }
                $movie = new Movie($videoSource->base_url, $videoSource->location, $path);
                $movies[] = $movie;
            }
            foreach ($movies as $movie) {
                if ($movie->isNew()) {
                    $newVideoIds[] = $movie->getVideoId();
                }
                $movie->writeToDb();
            }
        } else if ($videoSource->media_type === Enumerations::MediaType_TvShow) {
            //for now, assume any file or folder found under a tv show folder will just re-import the entire tv show folder
            $paths = [];
            if ($pathIsFile === true) {
                $paths = [$path];
            } else {
                $paths = getVideosFromDir($path);
            }

            $shows = [];
            foreach ($paths as $path) {
                if (strpos(strtolower($path), ".extra.") !== false) {
                    continue;
                }
                $episode = new TvEpisode($videoSource->base_url, $videoSource->location, $path);
                if ($episode->isNew()) {
                    $newVideoIds[] = $episode->getVideoId();
                }
                //get the name of the tv show for this episode
                $showName = $episode->getShowName();
                $show = null;
                if (isset($shows[$showName]) === false) {
                    $showPath = $videoSource->location . '/' . $showName;
                    $show = new TvShow($videoSource->base_url, $videoSource->location, $showPath);
                    $shows[$showName] = $show;
                } else {
                    $show = $shows[$showName];
                }
                //set the tv show object for this episode;
                $episode->tvShow = $show;
                $show->addEpisode($episode);
            }

            //at this point we have all of the episodes loaded into the tv shows that we care about
            foreach ($shows as $show) {
                if ($show->isNew()) {
                    $newVideoIds[] = $show->getVideoId();
                }
                $show->writeToDb();
                foreach ($show->episodes as $episode) {
                    $episode->writeToDb();
                }
            }
        }
        return $newVideoIds;
    }

}

?>