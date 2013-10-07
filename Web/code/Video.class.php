<?php

include_once("database/Queries.class.php");
include_once("SimpleImage.class.php");
include_once("Enumerations.class.php");
include_once("Movie.class.php");
include_once("TvShow.class.php");
include_once("TvEpisode.class.php");

include_once(dirname(__FILE__) . "/../config.php");
include_once(dirname(__FILE__) . "/functions.php");

abstract class Video {

    abstract function fetchMetadata();

    const NoMetadata = "0000-00-00 00:00:00"; //this will never be a valid date, so use it for invalid metadata dates
    const SdImageWidth = 110; //110x150 
    const HdImageWidth = 210; // 210x270

    public $videoSourceUrl;
    public $videoSourcePath;
    public $fullPath;
    public $mediaType;
    public $title;
    public $plot = "";
    public $year;
    public $url;
    public $sdPosterUrl;
    public $hdPosterUrl;
    public $mpaa = "N/A";
    public $actorList = [];
    public $generatePosterMethod;
    public $videoId = null;
    //if this is set to true, you should refresh the video in the db when being updated
    public $refreshVideo;
    protected $metadata;
    protected $onlineMovieDatabaseId;
    protected $metadataFetcher;
    protected $filetype = null;
    protected $metadataLoaded = false;
    protected $runtime = -1;
    protected $runtimeInSeconds = 0;

    function __construct($videoSourceUrl, $videoSourcePath, $fullPath) {
        //save the important stuff
        $this->videoSourceUrl = $videoSourceUrl;
        $this->videoSourcePath = $videoSourcePath;
        $this->fullPath = $fullPath;

        //calculate anything extra that is needed
        $this->url = Video::EncodeUrl($this->getUrl());
        $this->sdPosterUrl = Video::EncodeUrl($this->getSdPosterUrl());
        $this->hdPosterUrl = Video::EncodeUrl($this->getHdPosterUrl());
        $this->title = $this->getVideoName();
        $this->generatePosterMethod = $this->getGeneratePosterMethod();
    }

    /**
     * 
     * @param type $videoId
     * @return Video $video
     */
    public static function loadFromDb($videoId) {

        $v = Queries::getVideo($videoId);
        //if no video was found, nothing more can be done
        if ($v === false) {
            return false;
        }
        switch ($v->media_type) {
            case Enumerations::MediaType_Movie:
                $video = new Movie($v->video_source_url, $v->video_source_path, $v->path);
                break;
            case Enumerations::MediaType_TvShow:
                $video = new TvShow($v->video_source_url, $v->video_source_path, $v->path);
                break;
            case Enumerations::MediaType_TvEpisode:
                $video = new TvEpisode($v->video_source_url, $v->video_source_path, $v->path);
                break;
        }

        $video->videoId = $v->video_id;
        $video->title = $v->title;
        $video->plot = $v->plot;
        $video->mpaa = $v->mpaa;
        $video->runtimeInSeconds = $v->running_time_seconds;
        return $video;
    }

    /**
     * Gets the percent of the video that has already been watched
     * @return int - the percent complete this video is from being watched
     */
    public function progressPercent() {
        $current = Video::GetVideoStartSeconds($this->videoId);
        $totalLength = $this->getLengthInSeconds();
        //if we don't have numbers avaiable that will give us a percent, assume the percent is zero
        if ($totalLength === false || $totalLength === 0 || $current === 0) {
            return 0;
        } else {
            $percent = intval(($current / $totalLength ) * 100);
            return $percent;
        }
    }

    protected $lengthInSeconds = false;

    public function getLengthInSeconds($force = false) {
        //if the lengthInSeconds has not yet been calculated, calculate it
        if ($this->lengthInSeconds == false && $force !== true) {
            //first, try to read the file, since it knows how long the video ACTUALLY is
            $seconds = $this->getLengthInSecondsFromFile();
            //if the seconds value was valid, return it
            if ($seconds !== false) {
                return $seconds;
            }
            //seconds was not able to be determined from the file. try reading it from the metadata.
            $seconds = $this->getLengthInSecondsFromMetadata();
            if ($seconds !== -1) {
                return $seconds;
            } else {
                return -1;
            }
        } else {
            return $this->lengthInSeconds;
        }
    }

    protected abstract function getLengthInSecondsFromMetadata();

    /**
     * Parses the mp4 video's metadata to find the full length of the video in seconds
     * @return int|boolean - the number of seconds if successful, false if unsuccessful
     */
    private function getLengthInSecondsFromFile() {
        //the mp4info class likes to spit out random crap. hide it with an output buffer
        ob_start();
        $result = @MP4Info::getInfo($this->fullPath);
        ob_end_clean();
        if ($result !== null && $result != false && $result->hasVideo === true) {
            return intval($result->duration);
        } else {
            return false;
        }
    }

    /**
     * Retrieves the number of seconds into the video the video was stopped at
     * @param int $videoId - the videoId of the video in question
     * @return int - the number of seconds into the video that the video was stopped at
     */
    public static function GetVideoStartSeconds($videoId) {
        return Queries::getVideoProgress(config::$globalUsername, $videoId);
    }

    /**
     * Retrieves the number of seconds into the video the video was stopped at
     * @param int $videoId - the videoId of the video in question
     * @return int - the number of seconds into the video that the video was stopped at
     */
    public function videoStartSeconds() {
        return Queries::getVideoProgress(config::$globalUsername, $this->getVideoId());
    }

    function getGeneratePosterMethod() {
        if (isset($_GET["generatePosters"])) {
            return $_GET["generatePosters"];
        } else {
            return Enumerations::GeneratePosters_None;
        }
    }

    /**
     * Returns the media type of this video. It could be Movie, Tv Show, or Tv Episode
     * @return Enumerations::MediaType - the media type of the video
     */
    function getMediaType() {
        return $this->mediaType;
    }

    /**
     * Given the url of an image, this function will pull down that poster and save it to the poster file path
     * @param type $posterUrl
     * @return boolean - true if successful, false if there was a problem
     */
    function downloadPoster($posterUrl) {
        $posterPath = $this->getPosterPath();
        $success = saveImageFromUrl($posterUrl, $posterPath);
        if ($success === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Generates the sd and hd images for this video's poster based on the generate posters method. (if none, no poster is generated, if missing, only missing posters
     * are generated. if all, then all posters are re-generated
     */
    function generatePosters() {
        switch ($this->generatePosterMethod) {
            case Enumerations::GeneratePosters_None:
                break;
            case Enumerations::GeneratePosters_Missing:
                //if the SD poster does not exist, generate it
                if (!file_exists($this->getSdPosterPath())) {
                    $this->generateSdPoster();
                }
                //if the HD poster does not exist, generate it
                if (!file_exists($this->getHdPosterPath())) {
                    $this->generateHdPoster();
                }
                break;
            case Enumerations::GeneratePosters_All:
                $this->generateSdPoster();
                $this->generateHdPoster();
                break;
        }
    }

    /**
     * Determine if there is a poster for this video
     * @return boolean - true if the poster exists, false if it does not
     */
    public function posterExists() {
        return file_exists($this->getPosterPath());
    }

    public function getVideoName() {
        //For now, just return the filename without the extension.
        return pathinfo($this->fullPath, PATHINFO_FILENAME);
    }

    protected function getUrl() {
        $relativePath = str_replace($this->videoSourcePath, "", $this->fullPath);
        $url = $this->videoSourceUrl . $relativePath;
        //encode the url and then restore the forward slashes and colons
        return $url;
    }

    protected function getFullPathToContainingFolder() {
        return pathinfo($this->fullPath, PATHINFO_DIRNAME) . "/";
    }

    protected function getFullUrlToContainingFolder() {
        $dirname = pathinfo($this->url, PATHINFO_DIRNAME);
        return $dirname . "/";
    }

    public static function EncodeUrl($url) {
        return str_replace(" ", "%20", $url);
    }

    /**
     * Determines the nfo file path. Does NOT check to make sure the file exists.
     * Returns the path for an nfo file named the same as the video file. i.e. MyTvEpisode.avi, MyTvEpisode.nfo
     * @return type
     */
    public function getNfoPath() {
        $p = $this->fullPath;
        $nfoPath = pathinfo($p, PATHINFO_DIRNAME) . "/" . pathinfo($p, PATHINFO_FILENAME) . ".nfo";
        return $nfoPath;
    }

    /**
     * Determines if this video HAS a metadata file (nfo file).
     * @return boolean - true if the nfo file was found, false if it was not found
     */
    public function nfoFileExists() {
        //get the path to the nfo file
        $nfoPath = $this->getNfoPath();
        //verify that the file exists
        if (file_exists($nfoPath) === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Determines whether or not the SD poster exists on disk
     */
    function sdPosterExists() {
        if (file_exists($this->getSdPosterPath())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determines whether or not the HD poster exists on disk
     */
    function hdPosterExists() {
        if (file_exists($this->getHdPosterPath())) {
            return true;
        } else {
            return false;
        }
    }

    function getPosterPath() {
        return $this->getFullPathToContainingFolder() . "folder.jpg";
    }

    function getSdPosterPath() {
        return $this->getFullPathToContainingFolder() . "folder.sd.jpg";
    }

    function getHdPosterPath() {
        return $this->getFullPathToContainingFolder() . "folder.hd.jpg";
    }

    function getPosterUrl() {
        return $this->getFullUrlToContainingFolder() . "folder.jpg";
    }

    function getSdPosterUrl() {
        return $this->getFullUrlToContainingFolder() . "folder.sd.jpg";
    }

    function getHdPosterUrl() {
        return $this->getFullUrlToContainingFolder() . "folder.hd.jpg";
    }

    /**
     * Generates an poster that is sized to the SD image specifications for the roku standard movie grid layout
     * The existing aspect ratio is retained
     * @param type $width
     * @return boolean - true if successful, false if file doesn't exist or failure

     */
    public function generateSdPoster($width = Video::SdImageWidth) {
        $posterPath = $this->getPosterPath();
        if (file_exists($posterPath)) {
            $image = new SimpleImage();
            //load the image
            try {
                $success = $image->load($posterPath);

                //resize the image
                $image->resizeToWidth($width);
                //re-save the image as folder-small.jpg
                $image->save($this->getSdPosterPath());
            } catch (ErrorException $e) {
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    /**
     * Generates an poster that is set to the HD image specifications for the roku standard movie grid layout. 
     * The existing aspect ratio is retained
     * @param type $width - optional width to override the standard. 
     * @return boolean - true if successful, false if file doesn't exist or failure
     */
    function generateHdPoster($width = Video::HdImageWidth) {
        $posterPath = $this->getPosterPath();
        if (file_exists($posterPath)) {
            $image = new SimpleImage();

            try {
                //load the image
                $image->load($this->getPosterPath());
                //resize the image
                $image->resizeToWidth($width);
                //re-save the image as folder-small.jpg
                $image->save($this->getHdPosterPath());
            } catch (ErrorException $e) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     *  Returns the filetype of the video based on the extension of the file
     * @return string - the filetype of the video based on its extension
     */
    public function getFiletype() {
        //if the filetype has not yet been determined, determine it
        if ($this->filetype === null) {
            $this->filetype = strtolower(pathinfo($this->fullPath, PATHINFO_EXTENSION));
        }
        return $this->filetype;
    }

    /**
     * Writes this video to the database. If it has not yet been added to the database, an insert is performed.
     * If it already exists, an update is performed.
     */
    public function writeToDb() {
        //make sure this video has the latest metadata loaded
        $this->loadMetadata();
        $videoId = $this->getVideoId();
        //if this is a video that does not yet exist in the database, create a new video
        if ($videoId === -1) {
            Queries::insertVideo($this->title, $this->plot, $this->mpaa, $this->year, $this->fullPath, $this->getFiletype(), $this->mediaType, $this->getNfoLastModifiedDate(), $this->videoSourcePath, $this->videoSourceUrl, $this->getLengthInSeconds());
        } else {
            //this is an existing video that needs to be updated. update it
            Queries::updateVideo($videoId, $this->title, $this->plot, $this->mpaa, $this->year, $this->fullPath, $this->getFiletype(), $this->mediaType, $this->getNfoLastModifiedDate(), $this->videoSourcePath, $this->videoSourceUrl, $this->getLengthInSeconds());
        }
        $this->videoId = $this->getVideoId(true);
    }

    /**
     * Modifies the public variables in this class in order to only write the necessary variables to the json file. 
     */
    public function prepForJsonification() {
        unset($this->videoSourceUrl);
        unset($this->videoSourcePath);
        unset($this->fullPath);
        unset($this->mediaType);
        unset($this->generatePosterMethod);
    }

    /**
     * Compares the last modified time of the metadata file currently attached to this video with the 
     * last modified time of the metadata that was added to the db. 
     * @return boolean - true if the metadata dates of the db and the file are equal, false if they are not
     */
    public function metadataInDatabaseIsUpToDate() {
        $nfoLastModifiedTime = $this->getNfoLastModifiedDate();

        $videoId = $this->getVideoId();
        //if the videoId is invalid, this is a new video and therefore the metadata in the db is out of date since it has not been added yet
        if ($videoId == -1) {
            //there is no info about this video in the db. obviously, the metadata is NOT up to date
            return false;
        } else {
            $dbLastModifiedNfoDate = Queries::getVideoMetadataLastModifiedDate($videoId);
            //if the two metadata modified dates are equal, then the metadata is up to date
            if (strcmp($nfoLastModifiedTime, $dbLastModifiedNfoDate) == 0) {
                return true;
            }
        }
        return false;
    }

    public function getVideoId($bForce = false) {
        if ($this->videoId === null || $bForce === true) {
            $this->videoId = Queries::getVideoIdByVideoPath($this->fullPath);
        }
        return $this->videoId;
    }

    protected function getNfoLastModifiedDate() {
        //if this movie has metadata
        if ($this->nfoFileExists()) {
            //get the path to the metadata
            $metadataPath = $this->getNfoPath();

            $modifiedDate = date("Y-m-d H:i:s", filemtime($metadataPath));
            return $modifiedDate;
        } else {
            return Video::NoMetadata;
        }
    }

    /**
     * This class should be overridden by the child classes
     * @return null
     */
    protected function getMetadataFetcher() {
        return null;
    }

    /**
     * Searches imdb to find the poster for this movie.
     * Previous file is deleted before attempting to fetch new file. So if this fails, the video folder will be imageless
     * 
     * Returns true if successful, returns false and echoes error if failure
     */
    public function fetchPoster() {

        $adapter = $this->getMetadataFetcher();
        return $this->downloadPoster($adapter->posterUrl());
    }

}

?>
