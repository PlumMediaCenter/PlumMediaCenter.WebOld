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
            $this->movies[] = $movie;
            $this->videos[] = $movie;
            $this->movieCount++;
        }
        VideoController::SortVideosByTitle($this->movies);
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
        VideoController::SortVideosByTitle($this->tvShows);
        return $this->tvShows;
    }

    /**
     * Loads this library object totally from the filesystem. This means scanning each video source directory for 
     * videos. 
     */
    public function loadFromFilesystem()
    {
        //for each movie
        $success = $this->loadMoviesFromFilesystem();
        $success = $success && $this->loadTvShowsFromFilesystem();
        return $success;
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
                $video = new Movie($source->base_url, $source->location, $fullPathToFile);
                $this->movies[] = $video;
                $this->videos[] = $video;
                $this->movieCount++;
            }
        }
        return true;
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
                $tvShow = new TvShow($source->base_url, $source->location, $fullPathToFile);

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
        return true;
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

    /**
     * Forces every video loaded into memory in this library object to be written to the database. 
     * Then any videos that are no longer in this library are removed from the database
     */
    public function writeToDb()
    {
        //writes every video to the database. If it is a new video, it will automatically be added. If it is an existing
        //video, it will be updated
        $totalSuccess = true;
        foreach ($this->videos as $video) {
            $thisVideoSuccess = $video->writeToDb();
            $totalSuccess = $totalSuccess && $thisVideoSuccess;
        }

        //delete any videos that don't exist anymore
        Video::DeleteMissingVideos();

        //return success or failure. If at least one item failed, this will be returned as a failure
        return $totalSuccess;
    }

    public static function GetCategoryNames()
    {
        //get the full list of categories for this user
        $userCategoryNames = DbManager::SingleColumnQuery("
            select concat('list:', name)
            from list 
            where user_id = " . Security::GetUserId() . "
                and name <> 'My List'
        ");

        //get the distinct list of keywords
        $keywordNames = DbManager::SingleColumnQuery("
            select distinct concat('genre:', genre)
            from video_genre
            order by genre asc
        ");

        //ignore 'Recently Updated' for now because the library generator auto-saves every video by default
        return array_merge(['Recently Watched', 'list:My List'], $userCategoryNames, ['Recently Added', 'TV Shows', 'Movies'], $keywordNames);
    }

    public static function GetCategories($categoryNames = null)
    {
        if ($categoryNames === null) {
            $categoryNames = Library::GetCategoryNames();
        }

        $nonCacheCategories = ['Recently Watched'];
        $disableCache = true;
        $lib = new Library();
        $allVideoIds = [];

        $categories = [];
        foreach ($categoryNames as $categoryName) {
            $cacheName = str_replace(':', '-', "category-$categoryName");
            $categoryTitle = $categoryName;
            if (
                $disableCache === false &&
                Library::CacheExists($cacheName) &&
                in_array($categoryName, $nonCacheCategories) === false
            ) {
                $categories[$categoryName] = Library::GetFromCache($cacheName);
            }

            // if we already have this category in the list, don't get it again
            if (isset($categories[$categoryName])) {
                continue;
            }

            $videoIds = [];

            if ($categoryName === 'Recently Watched') {
                $videoIds = Library::GetRecentlyWatchedVideoIds();
            } else if ($categoryName === 'Recently Added') {
                $videoIds = Library::GetRecentlyAddedVideoIds(30);
            } else if ($categoryName === 'Recently Updated') {
                $videoIds = Library::GetRecentlyUpdatedVideoIds(30);
            } else if ($categoryName === "TV Shows") {
                $lib->loadTvShowsFromDatabase(false);
                $videoIds = pickProp($lib->tvShows, 'videoId', 'int');
            } else if ($categoryName === "Movies") {
                $lib->loadMoviesFromDatabase();
                $videoIds = pickProp($lib->movies, 'videoId', 'int');
            } else if (strpos($categoryName, 'list:') === 0) {
                $categoryTitle = substr($categoryName, strlen('list:'));
                $videoIds = Queries::GetVideoIdsForListName($categoryTitle);
            } else if (strpos($categoryName, 'genre:') === 0) {
                $categoryTitle = substr($categoryName, strlen('genre:'));
                $videoIds = Queries::GetVideoIdsForGenre($categoryTitle);
            }

            //create the new category
            $categories[$categoryName] = new Category($categoryName, $videoIds, $categoryTitle);
            //merge this category's video IDs into the full list
            $allVideoIds = array_merge($allVideoIds, $videoIds);
        }

        $distinctVideoIds = distinct($allVideoIds);
        $result = (object) [];
        $videos = Video::GetVideos($distinctVideoIds);
        //make a map of videos indexed by videoId
        $result->videos = [];
        foreach ($videos as $video) {
            $result->videos[$video->videoId]  = $video;
        }
        $result->categories = [];
        foreach ($categoryNames as $categoryName) {
            $result->categories[] = $categories[$categoryName];
        }
        return $result;
    }

    public static function CacheExists($cacheName)
    {
        $cachePath = dirname(__FILE__) . '/../cache/' . $cacheName;
        return file_exists($cachePath);
    }

    public static function GetFromCache($cacheName)
    {
        $cachePath = dirname(__FILE__) . '/../cache/' . $cacheName;
        return json_decode(file_get_contents($cachePath));
    }

    public static function PutCache($cacheName, $obj)
    {
        if (!file_exists(dirname(__FILE__) . '/../cache/')) {
            mkdir(dirname(__FILE__) . '/../cache/', 0777, true);
        }
        $cachePath = dirname(__FILE__) . '/../cache/' . $cacheName;
        file_put_contents($cachePath, json_encode($obj));
    }

    public static function ClearCache()
    {
        $dir = dirname(__FILE__) . '/../cache/';
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                rrmdir($file);
            } else {
                unlink($file);
            }
        }
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
            $tvShows = DbManager::Query(
                "select video_id as episode_video_id, tv_show_video_id as video_id, '" . Enumerations::MediaType_TvShow . "' as media_type"
                    . " from tv_episode_v"
                    . " where video_id " . DbManager::GenerateInStatement($tvEpisodeVideoIds) . " "
                    . "order by field(video_id, " . implode(",", $videoIds) . ")"
            );
            foreach ($tvShows as $show) {
                $showLookup[$show->episode_video_id] = $show;
            }
        }

        $resultVideoIds = [];
        $videoIdLookup = [];
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

    public static function GetRecentlyAddedVideoIds($numberOfDays)
    {
        $recentVideoIds = DbManager::SingleColumnQuery("
            select 
                video_id 
            from 
                video 
            where 
                date_added between DATE_SUB(NOW(), INTERVAL $numberOfDays DAY) AND NOW() 
            order by date_added desc 
            limit 50
        ");
        $videoIds = Library::ReduceVideoIds($recentVideoIds);
        return $videoIds;
    }

    public static function GetRecentlyUpdatedVideoIds($numberOfDays)
    {
        $recentVideoIds = DbManager::SingleColumnQuery("
            select video_id 
            from video 
            where date_modified between DATE_SUB(NOW(), INTERVAL $numberOfDays DAY) AND NOW() 
            and date_modified > date_added
            order by date_added desc
            limit 50
        ");
        $videoIds = Library::ReduceVideoIds($recentVideoIds);
        return $videoIds;
    }

    public static function GetRecentlyWatchedVideoIds()
    {
        $videoIds = DbManager::SingleColumnQuery("
                        select video_id 
                        from recently_watched 
                        where user_id = '" . config::$defaultUserId . "' 
                        order by date_watched desc
                        limit 20");
        return arrayToInt($videoIds);
    }
}
