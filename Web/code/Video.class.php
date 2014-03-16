<?php

include_once(dirname(__FILE__) . "/Security.class.php");
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

    abstract protected function loadCustomMetadata();

    abstract function getNfoReader();

    const NoMetadata = "0000-00-00 00:00:00"; //this will never be a date found in the metadata, so use it for invalid metadata dates
    const SdImageWidth = 110; //110x150 
    const HdImageWidth = 210; // 210x270
    const baseQuery = "select   media_type as mediaType,  
                                title as title, 
                                plot as plot,  
                                release_date as year,  
                                url as url,  
                                sd_poster_url as sdPosterUrl, 
                                hd_poster_url as hdPosterUrl, 
                                mpaa as mpaa, 
                                video_id as videoId 
                                from video";

    protected $videoSourceUrl;
    protected $videoSourcePath;
    protected $fullPath;
    public $mediaType;
    public $title;
    public $plot = "";
    public $year;
    public $url;
    public $sdPosterUrl;
    public $hdPosterUrl;
    public $mpaa = "N/A";
    public $actorList = [];
    public $videoId = null;
    public $genres = [];
    public $runtime = -1;
    protected $metadata;
    protected $onlineVideoDatabaseId;
    protected $metadataFetcher;
    protected $filetype = null;
    protected $metadataLoaded = false;
    protected $runtimeInSeconds = 0;
    protected $nfoReader = null;

    function __construct($videoSourceUrl, $videoSourcePath, $fullPath) {
        //save the important stuff
        $this->videoSourceUrl = $videoSourceUrl;
        $this->videoSourcePath = str_replace("\\", "/", realpath($videoSourcePath)) . "/";
        $fullPathRealPath = realpath($fullPath);
        if($fullPathRealPath === false){
            throw new Exception("Unable to construct a video object at path $fullPath: path does not exist");
        }
        $this->fullPath = str_replace("\\", "/", $fullPathRealPath);

        //if this video does not exist, throw a new exception
        if (file_exists($this->fullPath) === false) {
            //throw new Exception("Video file does not exist at path $this->fullPath");
        }

        //calculate anything extra that is needed
        $this->url = Video::EncodeUrl($this->getUrl());
        $this->sdPosterUrl = $this->getActualSdPosterUrl();
        $this->hdPosterUrl = $this->getActualHdPosterUrl();
        $this->title = $this->getVideoName();
    }

    public function getFullPath() {
        return $this->fullPath;
    }

    /**
     * Returns the media type of this video. It could be Movie, Tv Show, or Tv Episode
     * @return Enumerations\MediaType - the media type of the video
     */
    public function getMediaType() {
        return $this->mediaType;
    }

    public function getVideoSourceUrl() {
        return $this->videoSourceUrl;
    }

    public function getVideoSourcePath() {
        return $this->videoSourcePath;
    }

    /**
     * Creates a video object of the media type of the video specified. Loads all metadata from the database and
     * populates the class with that metadata.
     * @param int $videoId
     * @return Movie|TvShow|TvEpisode $video
     */
    public static function GetVideo($videoId) {
        $videoRow = Queries::getVideo($videoId);
        //if no video was found, nothing more can be done
        if ($videoRow === false) {
            return false;
        }
       return Video::GetVideoFromDataRow($videoRow);
    }
    
    /**
     * Converts a row from the video table into a video object
     * @param [] $row
     * @return Movie\TvShow\TvEpisode
     */
    public static function GetVideoFromDataRow($row){
        $video = null;
        switch ($row->media_type) {
            case Enumerations\MediaType::Movie:
                $video = new Movie($row->video_source_url, $row->video_source_path, $row->path);
                break;
            case Enumerations\MediaType::TvShow:
                $video = new TvShow($row->video_source_url, $row->video_source_path, $row->path);
                break;
            case Enumerations\MediaType::TvEpisode:
                $video = new TvEpisode($row->video_source_url, $row->video_source_path, $row->path);
                break;
        }
        $video->videoId = intval($row->video_id);
        $video->title = $row->title;
        $video->runtimeInSeconds = $row->running_time_seconds;
        $video->plot = $row->plot;
        $video->mpaa = $row->mpaa;
        $video->year = $row->release_date;
        $video->runtime = $row->running_time_seconds;
        return $video;
    }

    /**
     * Loads the genres for this video 
     */
    public function loadGenres() {
        $genres = Queries::GetVideoGenres($this->videoId);
        if ($genres != false) {
            $this->genres = $genres;
        } else {
            $this->genres = [];
        }
        return $this->genres;
    }

    /**
     * Determines if this is a new video
     */
    public function isNew() {
        //if this video does NOT have a video id, then it does not exist in the database. It is new.
        if ($this->getVideoId() === -1) {
            return true;
        } else {
            return false;
        }
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
            $this->lengthInSeconds = $this->getLengthInSecondsFromFile();
            //if the seconds value was valid, return it
            if ($this->lengthInSeconds !== false) {
                return $this->lengthInSeconds;
            }
            //seconds was not able to be determined from the file. try reading it from the metadata.
            $this->lengthInSeconds = $this->getLengthInSecondsFromMetadata();
            if ($this->lengthInSeconds !== -1) {
                return $this->lengthInSeconds;
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
    public static function GetVideoStartSeconds($videoId, $finishedBuffer = null) {
        $v = Video::GetVideo($videoId);
        if ($v == false) {
            return 0;
        }
        return $v->videoStartSeconds($finishedBuffer);
    }

    /**
     * Retrieves the number of seconds into the video the video was stopped at
     * @param int $videoId - the videoId of the video in question
     * @return int - the number of seconds into the video that the video was stopped at
     */
    public function videoStartSeconds($finishedBuffer = 45) {
        $progress = Queries::getVideoProgress(Security::GetUsername(), $this->videoId);
        $totalVideoLength = $this->getLengthInSeconds();
        if (($progress + $finishedBuffer > $totalVideoLength) || ($progress == -1)) {
            return 0;
        } else {
            return $progress;
        }
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
        $this->generateSdPoster();
        $this->generateHdPoster();
    }

    /**
     * Determine if there is a poster for this video
     * @return boolean - true if the poster exists, false if it does not
     */
    public function posterExists() {
        return file_exists($this->getPosterPath());
    }

    public function getVideoName() {
        //the video name is the name of its containing folder
        $folderName = pathinfo(pathinfo($this->fullPath, PATHINFO_DIRNAME) . "/", PATHINFO_FILENAME);
        //rip any year in parenthesies off the end
        $matches = null;
        $folderName = preg_replace("/(\(\d\d\d\d\))/i", "", $folderName);
        //trim spaces from either side of the name
        $folderName = trim($folderName);
        return $folderName;

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
        return Video::EncodeUrl($this->getFullUrlToContainingFolder() . "folder.jpg");
    }

    function getSdPosterUrl() {
        return Video::EncodeUrl($this->getFullUrlToContainingFolder() . "folder.sd.jpg");
    }

    function getHdPosterUrl() {
        $hdPosterUrl = $this->getFullUrlToContainingFolder() . "folder.hd.jpg";
        return Video::EncodeUrl($hdPosterUrl);
    }

    function getActualHdPosterUrl() {
        if ($this->hdPosterExists() == true) {
            return Video::EncodeUrl($this->getHdPosterUrl());
        } else {
            $url = fileUrl(__FILE__) . "/../img/posters/" . $this->getBlankPosterName() . ".hd.jpg";
            $url = url_remove_dot_segments($url);
            return Video::EncodeUrl($url);
        }
    }

    function getActualSdPosterUrl() {
        if ($this->sdPosterExists() == true) {
            return Video::EncodeUrl($this->getSdPosterUrl());
        } else {
            $url = fileUrl(__FILE__) . "/../img/posters/" . $this->getBlankPosterName() . ".sd.jpg";
            $url = url_remove_dot_segments($url);
            return Video::EncodeUrl($url);
        }
    }

    function getActualPosterUrl() {
        if ($this->PosterExists() == true) {
            return Video::EncodeUrl($this->getPosterUrl());
        } else {
            $url = fileUrl(__FILE__) . "/../img/posters/" . $this->getBlankPosterName() . ".jpg";
            $url = url_remove_dot_segments($url);
            return Video::EncodeUrl($url);
        }
    }

    function getBlankPosterName() {
        return "BlankPoster";
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
        $success = false;
        //make sure this video has the latest metadata loaded
        $this->loadMetadata();
        $videoId = $this->getVideoId();
        //if this is a video that does not yet exist in the database, create a new video
        if ($videoId === -1) {
            //insert into this video into the video table
            $success = Queries::insertVideo($this->title, $this->plot, $this->mpaa, $this->year, $this->fullPath, $this->getFiletype(), $this->mediaType, $this->getNfoLastModifiedDate(), $this->videoSourcePath, $this->videoSourceUrl, $this->getLengthInSeconds(), $this->url, $this->getHdPosterUrl(), $this->getSdPosterUrl());
        } else {
            //this is an existing video that needs to be updated. update it
            $success = Queries::updateVideo($videoId, $this->title, $this->plot, $this->mpaa, $this->year, $this->fullPath, $this->getFiletype(), $this->mediaType, $this->getNfoLastModifiedDate(), $this->videoSourcePath, $this->videoSourceUrl, $this->getLengthInSeconds(), $this->url, $this->getHdPosterUrl(), $this->getSdPosterUrl());
        }
        $this->videoId = $this->getVideoId(true);
        if ($this->videoId != -1 && $success === true) {
            //insert genres
            Queries::insertVideoGenres($this->videoId, $this->genres);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Modifies the public variables in this class in order to only write the necessary variables to the json file. 
     */
    public function prepForJsonification() {
        $this->hdPosterUrl = $this->getActualHdPosterUrl();
        $this->sdPosterUrl = $this->getActualSdPosterUrl();
    }

    protected function deleteMetadata() {
        $metadataDestination = $this->getNfoPath();
        //if an old metadata file already exists, delete it.
        if (is_file($metadataDestination) == true) {
            //delete the file
            unlink($metadataDestination);
        }
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
     * Loads pertinent metadata from the nfo file into this class
     * @param bool $force -- optional. forces metadata to be loaded, even if it has already been loaded
     * @return boolean
     */
    public function loadMetadata($force = false) {
        //if the metadata hasn't been loaded yet, or force is true (saying do it anyway), load the metadata
        if ($this->metadataLoaded === false || $force === true) {
            //get the path to the nfo file
            $nfoPath = $this->getNfoPath();
            //verify that the file exists
            if (file_exists($nfoPath) === false) {
                return false;
            }
            $reader = $this->getNfoReader();
            $loadSuccess = $reader->loadFromFile($nfoPath);
            //if the nfo reader loaded successfully, pull the important information into this class
            if ($loadSuccess) {
                //if the title was found, use it. otherwise, keep the filename tile that was loaded during the constructor
                $this->title = $reader->title !== null ? $reader->title : $this->title;
                $this->plot = $reader->plot !== null ? $reader->plot : "";
                $this->mpaa = $reader->mpaa !== null ? $reader->mpaa : $this->mpaa;
                $this->actorList = $reader->actors;

                $success = $this->loadCustomMetadata();
                return $success;
                //  $this->year = $reader->getYear() !== null ? $reader->getYear() : "";
                // $this->runtime = $reader->runtime;
            } else {
                return false;
            }
        }
        //if made it to here, all is good. return true
        return true;
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

    public function setOnlineVideoDatabaseId($id) {
        $this->onlineVideoDatabaseId = $id;
    }

    /**
     * Returns a Video Metadata Fetcher. If we have the Online Video Database ID, use that. Otherwise, use the folder name.
     * @param boolean $refresh - if set to true, the metadata fetcher is recreated. otehrwise, a cached one is used if present
     * @param int $onlineVideoDatabaseId - the id of the online video database used to reference this video. 
     *                                     If an id was present, use it. If not, see if there is a global one for the class. if not, search by title
     * @return MovieMetadataFetcher|TvShowMetadataFetcher 
     */
    protected function getMetadataFetcher($refresh = false, $onlineVideoDatabaseId = null) {
        //If an id was present, use it. If not, see if there is a global one for the class. if not, search by title
        $id = $this->onlineVideoDatabaseId;
        $id = $onlineVideoDatabaseId != null ? $onlineVideoDatabaseId : $id;
        if ($this->metadataFetcher == null || $refresh == true) {
            include_once(dirname(__FILE__) . "/MetadataFetcher/MovieMetadataFetcher.class.php");
            $this->metadataFetcher = $this->getMetadataFetcherClass();
            if ($id != null) {
                $this->metadataFetcher->searchById($id);
            } else {
                $this->metadataFetcher->searchByTitle($this->getVideoName());
            }
        }
        return $this->metadataFetcher;
    }

    /**
     * Delete a video from this application. 
     * @param int $videoId
     */
    public static function DeleteVideo($videoId) {
        $s1 = DbManager::NonQuery("delete from video_genre where video_id = $videoId");
        $s2 = DbManager::NonQuery("delete from watch_video where video_id = $videoId");
        $s3 = DbManager::NonQuery("delete from tv_episode where video_id = $videoId");
        $s4 = DbManager::NonQuery("delete from video where video_id = $videoId");
        return $s1 && $s2 && $s3 && $s4;
    }

    /**
     * compares two videos to sort them by title alphabetically
     * @param Video $video
     */
    public static function CompareTo($video1, $video2) {
        return strcmp($video1->title, $video2->title);
    }

}

?>
