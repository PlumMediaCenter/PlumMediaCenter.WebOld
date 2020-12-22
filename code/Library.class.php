<?php

include_once("database/Queries.class.php");
include_once("Video.class.php");
include_once("Category.class.php");
include_once("controllers/VideoController.php");

class Library
{

    public $movies = [];
    private $movieCount = 0;
    public $tvShows = [];
    private $tvShowCount = 0;
    public $tvEpisodes = [];
    private $tvEpisodeCount = 0;
    //contains a list of all videos, a combination of movies, tv shows and tv episodes
    public $videos = [];

    public function __construct()
    {
        //set the time limit for this script to be 10 minutes. If it takes any longer than that, there's something wrong
        set_time_limit(600);
    }

    /**
     * Returns the number of movies in the library
     * @return int - the number of movies in the library
     */
    public function getMovieCount()
    {
        return $this->movieCount;
    }

    /**
     * Fetches any missing metadata for the videos and fetches any missing posters for the videos and generates posters for
     * any video that doesn't have the posters generated yet.
     */
    public function fetchMissingMetadataAndPosters()
    {
        /* @var $video Video   */
        foreach ($this->videos as $video) {
            //skip this video if it's not an object
            if (is_object($video) == false) {
                continue;
            }
            try {
                if ($video->nfoFileExists() == false) {
                    $video->fetchMetadata();
                }
                if ($video->posterExists() == false) {
                    $video->fetchPoster();
                }
                if ($video->sdPosterExists() == false || $video->hdPosterExists() == false) {
                    $video->generatePosters();
                }
            } catch (Exception $e) {
            }
        }
        return true;
    }

    /**
     * Returns the number of tv shows found in the library
     * @return int - the number of tv shows in the library
     */
    public function getTvShowCount()
    {
        return $this->tvShowCount;
    }

    /**
     * Returns the number of episodes found in the library
     * @return int - the number of episodes found in the library
     */
    public function getTvEpisodeCount()
    {
        return $this->tvEpisodeCount;
    }

    /**
     * Loads all movies and tv shows from the database
     */
    public function loadFromDatabase()
    {
        $this->videos = [];
        $success = true;
        $this->loadMoviesFromDatabase();
        $this->loadTvShowsFromDatabase();
        return $success;
    }

    /**
     * Populates the movies array with all movies found in the database. All metadata is loaded into the movies. 
     * @return Movie[] - returns the array of movies loaded from the database
     */
    public function loadMoviesFromDatabase()
    {
        $this->movies = [];
        $this->movieCount = 0;
        $videoIds = Queries::GetVideoIds(Enumerations::MediaType_Movie);
        foreach ($videoIds as $videoId) {
            $movie = Video::GetVideo($videoId);
            //only include movies that actually exist on disk (i.e. haven't been renamed or deleted since the last library scan)
            if ($movie->exists()) {
                $this->movies[] = $movie;
                $this->videos[] = $movie;
                $this->movieCount++;
            }
        }
        return $this->movies;
    }

    /**
     * Loads an array of all tv shows found in the database. All metadata is loaded into the tv show objects. 
     * @return TvShow[] - returns the tv shows in the library that was loaded from the database
     */
    public function loadTvShowsFromDatabase($loadEpisodes = true)
    {
        $this->tvShows = [];
        $this->tvEpisodes = [];
        $this->tvShowCount = 0;
        $this->tvEpisodeCount = 0;
        $videoIds = Queries::GetVideoIds(Enumerations::MediaType_TvShow);
        foreach ($videoIds as $videoId) {
            $tvShow = Video::GetVideo($videoId);
            $this->tvShows[] = $tvShow;
            $this->videos[] = $tvShow;
            $this->tvShowCount++;

            if (is_object($tvShow) == false) {
                continue;
            }
            if ($loadEpisodes) {
                //load all of the episodes for this tv show
                $tvShow->loadEpisodesFromDatabase();
                $this->videos = array_merge($this->videos, $tvShow->episodes);
                $this->tvEpisodes = array_merge($this->tvEpisodes, $tvShow->episodes);
                $this->tvEpisodeCount += $tvShow->episodeCount;
            }
        }
        return $this->tvShows;
    }

    /**
     * Loads this library object totally from the filesystem. This means scanning each video source directory for 
     * videos. 
     */
    public function loadFromFilesystem()
    {
        $errors =  array_merge(
            $this->loadMoviesFromFilesystem(),
            $this->loadTvShowsFromFilesystem()
        );
        return $errors;
    }

    /**
     * Loads the movies array with movies found in the different video sources marked as movie sources
     */
    public function loadMoviesFromFilesystem()
    {
        //list of all video sources
        $movieSources = Queries::getVideoSources(Enumerations::MediaType_Movie);
        $this->movies = [];
        $this->movieCount = 0;
        foreach ($movieSources as $source) {
            //get a list of each video in this movies source folder
            $listOfAllFilesInSource = getVideosFromDir($source->location);

            foreach ($listOfAllFilesInSource as $fullPathToFile) {
                //if the movie contains the word ".extra." or '.trailer.', skip it for now. Eventually those will get added to the library
                if (
                    strpos(strtolower($fullPathToFile), ".extra.") !== false ||
                    strpos(strtolower($fullPathToFile), ".trailer.") !== false ||
                    strpos(strtolower($fullPathToFile), ".preview.") !== false ||
                    strpos(strtolower($fullPathToFile), ".recap.") !== false ||
                    strpos(strtolower($fullPathToFile), '/extras/') !== false ||
                    strpos(strtolower($fullPathToFile), '\\extras\\') !== false
                ) {
                    // echo "<br/>Skipping video: " . $fullPathToFile;

                    continue;
                }
                // echo "<br/>Processing video file: " . $fullPathToFile;

                //create a new Movie object
                $video = new Movie($source->id, $source->base_url, $source->location, $fullPathToFile);
                $this->movies[] = $video;
                $this->videos[] = $video;
                $this->movieCount++;
            }
        }
        return [];
    }

    /**
     * loads the libary TvShow object with tv shows found in the source paths marked as tv show sources
     * @return TvShow[] - the array of TvShows found at the source path
     */
    public function loadTvShowsFromFilesystem()
    {
        $this->tvShows = [];
        $this->tvEpisodes = [];
        $this->tvEpisodeCount = 0;
        $this->tvShowCount = 0;
        $tvShowSources = Queries::getVideoSources(Enumerations::MediaType_TvShow);
        //for every tv show file location, get all tv shows from that location
        foreach ($tvShowSources as $source) {
            //get a list of every folder in the current video source directory, since the required tv show structure is
            //  TvShowsFolder/Name Of Tv Show/files.....
            //get a list of each video in this tv shows folder
            $listOfAllFilesInSource = getFoldersFromDirectory($source->location);

            //spin through every folder in the source location
            foreach ($listOfAllFilesInSource as $fullPathToFile) {
                //if the current file is a video file that we can add to our library
                //create a new Movie object
                $tvShow = new TvShow($source->id, $source->base_url, $source->location, $fullPathToFile);

                //tell the tv show to scan subdirectories for tv episodes
                $tvShow->loadTvEpisodesFromFilesystem();

                //if this tv show has at least one season (which means it has at least one episode), then add it to the list
                if (count($tvShow->seasons) > 0) {
                    $this->tvShows[] = $tvShow;
                    $this->videos[] = $tvShow;
                    $this->tvShowCount++;

                    //include episodes
                    $this->videos = array_merge($this->videos, $tvShow->episodes);
                    $this->tvEpisodes = array_merge($this->tvEpisodes, $tvShow->episodes);
                    $this->tvEpisodeCount += $tvShow->episodeCount;
                }
            }
        }
        return [];
    }

    public function sort()
    {

        //sort the movies and tv shows
        usort($this->movies, array($this, 'cmp'));
        usort($this->tvShows, array($this, 'cmp'));
    }

    function cmp($a, $b)
    {
        if (isset($a) && isset($b) && isset($a->name) && isset($b->name)) {
            return strcmp($b->name, $a->name);
        } else {
            return true;
        }
    }

    /**
     * Returns a set of stats (counts) based on the library.
     */
    public static function GetVideoCounts()
    {
        $stats = (object) [];
        $stats->videoCount = null;
        $stats->movieCount = null;
        $stats->tvShowCount = null;
        $stats->tvEpisodeCount = null;
        $counts = Queries::GetVideoCounts();
        if ($counts != false) {
            $stats->videoCount = $counts->movieCount + $counts->tvEpisodeCount;
            $stats->movieCount = $counts->movieCount + 0;
            $stats->tvShowCount = $counts->tvShowCount + 0;
            $stats->tvEpisodeCount = $counts->tvEpisodeCount + 0;
        }
        return $stats;
    }

    public function generate()
    {
        //delete any videos that don't exist anymore
        Video::DeleteMissingVideos();
        return [
            'errors' => array_merge(
                $this->loadFromFilesystem(),
                $this->writeToDb(),
                $this->fetchAndLoadMetadata()
            )
        ];
        return $result;
    }

    public function fetchAndLoadMetadata(){
                
        /* @var $video Video   */
        foreach ($this->videos as $video) {
            //skip this video if it's not an object
            if (is_object($video) == false) {
                continue;
            }

            $video->fetchMetadataIfMissing();
            //force the video to load metadata from the filesystem
            $video->loadMetadata(true);
            //write the video to the database
            $video->writeToDb();

            //if this is a tv show, write all of its children
            if ($video->mediaType === Enumerations::MediaType_TvShow) {
                $video->loadTvEpisodesFromFilesystem();
                $episodes = $video->getEpisodes();
                foreach ($episodes as $episode) {
                    $episode->fetchMetadataIfMissing();
                    $episode->loadMetadata(true);
                    $episode->writeToDb();
                }
            }
        }
        return [];
    }

    /**
     * Forces every video loaded into memory in this library object to be written to the database. 
     * Then any videos that are no longer in this library are removed from the database
     */
    public function writeToDb()
    {

        //writes every video to the database. If it is a new video, it will automatically be added. If it is an existing
        //video, it will be updated
        $errors = [];
        foreach ($this->videos as $video) {
            $errors = $video->writeToDb();
            if (count($errors) > 0) {
                $errors[] = "Failed to write $video->mediaType to db: \"$video->fullPath\": " . $errors[0];
                break;
                continue;
            }
        }

        //return success or failure. If at least one item failed, this will be returned as a failure
        return $errors;
    }

    /**
     * Takes a list of videoIDs and reduces them to tv show and movie ids (converts the episode ids to a single tv show id
     */
    public static function ReduceVideoIds($videoIds)
    {
        $videoRecords = DbManager::Query(
            "select video_id, media_type "
                . "from video "
                . "where video_id " . DbManager::GenerateInStatement($videoIds) . " "
                . "order by field(video_id, " . implode(",", $videoIds) . ")"
        );

        $tvEpisodeVideoIds = [];

        foreach ($videoRecords as $videoRecord) {
            if ($videoRecord->media_type === Enumerations::MediaType_TvEpisode) {
                //this is a tv episode. 
                $tvEpisodeVideoIds[] = $videoRecord->video_id;
            }
        }


        $showLookup = [];
        // get the tv show video records for all of these episodes
        if (count($tvEpisodeVideoIds) > 0) {
            $tvShows = DbManager::Query("
                select 
                    video_id as episode_video_id,
                    tv_show_video_id as video_id,
                    '" . Enumerations::MediaType_TvShow . "' as media_type
                    from
                        tv_episode_v
                    where 
                        video_id " . DbManager::GenerateInStatement($tvEpisodeVideoIds) . "
                    order by 
                        field(video_id, " . implode(",", $videoIds) . ")
            ");
            
            foreach ($tvShows as $show) {
                $showLookup[$show->episode_video_id] = $show;
            }
        }

        $resultVideoIds = [];
        $videoIdLookup = [];
        $videoId = null;
        foreach ($videoRecords as $videoRecord) {
            if ($videoRecord->media_type === Enumerations::MediaType_Movie || $videoRecord->media_type === Enumerations::MediaType_TvShow) {
                $videoId = $videoRecord->video_id;
            } else {
                if (isset($showLookup[$videoRecord->video_id])) {
                    //this is an episode. go get its show record
                    $show = $showLookup[$videoRecord->video_id];
                    $videoId = $show->video_id;
                } else {
                    //this is an orphaned tv episode...skip this video
                }
            }
            if (!isset($videoIdLookup[$videoId])) {
                $videoIdLookup[$videoId] = $videoId;
                $resultVideoIds[] = (int) $videoId;
            }
        }
        return $resultVideoIds;
    }

    /**
     * Add a new media item to the library
     * @param int $videoSourceId - if null, attempt to auto-detect it
     * @param string $path
     */
    public static function AddNewMediaItem($videoSourceId, $path)
    {
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
                $movie = new Movie($videoSource->id, $videoSource->base_url, $videoSource->location, $path);
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
                $episode = new TvEpisode($videoSource->id, $videoSource->base_url, $videoSource->location, $path);
                if ($episode->isNew()) {
                    $newVideoIds[] = $episode->getVideoId();
                }
                //get the name of the tv show for this episode
                $showName = $episode->getShowName();
                $show = null;
                if (isset($shows[$showName]) === false) {
                    $showPath = $videoSource->location . '/' . $showName;
                    $show = new TvShow($videoSource->id, $videoSource->base_url, $videoSource->location, $showPath);
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
