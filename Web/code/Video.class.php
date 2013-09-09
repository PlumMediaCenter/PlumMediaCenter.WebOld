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
    public $posterExists;
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

    function __construct($videoSourceUrl, $videoSourcePath, $fullPath) {
        //save the important stuff
        $this->videoSourceUrl = $videoSourceUrl;
        $this->videoSourcePath = $videoSourcePath;
        $this->fullPath = $fullPath;

        //calculate anything extra that is needed
        $this->url = $this->encodeUrl($this->getUrl());
        $this->sdPosterUrl = $this->encodeUrl($this->getSdPosterUrl());
        $this->hdPosterUrl = $this->encodeUrl($this->getHdPosterUrl());
        $this->posterExists = $this->posterExists();
        $this->title = $this->getVideoName();
        $this->generatePosterMethod = $this->getGeneratePosterMethod();
        $this->generatePosters();
    }

    /**
     * 
     * @param type $videoId
     * @return Video $video
     */
    public static function loadFromDb($videoId) {

        $v = Queries::getVideo($videoId);
        switch ($v->media_type) {
            case Enumerations::MediaType_Movie:
                $video = new Movie($v->video_source_url, $v->video_source_path, $v->path);
                break;
            case Enumerations::MediaType_TvShow:
                $video = new TvShow($v->video_source_url, $v->video_source_path, $v->path);
                break;
            case Enumerations::MediaType_TvEpisode:
                $video = new TvShow($v->video_source_url, $v->video_source_path, $v->path);
                break;
        }

        $video->videoId = $v->video_id;
        $video->title = $v->title;
        $video->plot = $v->plot;
        $video->mpaa = $v->mpaa;
        return $video;
    }

    public function update() {
        //__construct($this->baseUrl, $this->basePath, $this->fullPath);
    }

    function getGeneratePosterMethod() {
        if (isset($_GET["generatePosters"])) {
            return $_GET["generatePosters"];
        } else {
            return Enumerations::GeneratePosters_None;
        }
    }

    function getMediaType() {
        return $this->mediaType;
    }

    /**
     * Given the url of an image, this function will pull down that poster and save it to the poster file path
     * @param type $posterUrl
     * @return boolean
     */
    function downloadPoster($posterUrl) {
        $posterImagePath = $this->getPosterPath();
        $success = saveImageFromUrl($posterUrl, $posterImagePath);
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

    protected function encodeUrl($url) {
        return str_replace(" ", "%20", $url);
    }

    protected function getFullPathToContainingFolder() {
        return pathinfo($this->fullPath, PATHINFO_DIRNAME) . "/";
    }

    protected function getFullUrlToContainingFolder() {
        $dirname = pathinfo($this->url, PATHINFO_DIRNAME);
        return $dirname . "/";
    }

    /**
     * Determines the nfo file path. Does NOT check to make sure the file exists.
     * Returns the path for an nfo file named the same as the video file. i.e. MyTvEpisode.avi, MyTvEpisode.nfo
     * @return type
     */
    function getNfoPath() {
        $p = $this->fullPath;
        $nfoPath = pathinfo($p, PATHINFO_DIRNAME) . "/" . pathinfo($p, PATHINFO_FILENAME) . ".nfo";
        return $nfoPath;
    }

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
     * Wrapper function for nfoFileExists, for older codebase support
     */
    public function hasMetadata() {
        return $this->nfoFileExists();
    }

    /**
     * Loads pertinent metadata from the nfo file into this class
     * @param bool $force -- optional. forces metadata to be loaded, even if it has already been loaded
     * @return boolean
     */
    protected function loadMetadata($force = false) {
        //if the metadata hasn't been loaded yet, or force is true (saying do it anyway), load the metadata
        if ($this->metadataLoaded === false || $force === true) {
            //get the path to the nfo file
            $nfoPath = $this->getNfoPath();
            //verify that the file exists
            if (file_exists($nfoPath) === false) {
                return false;
            }
            //load the nfo file as an xml file 
            //hide any xml errors that may pop up
            // $current_error_reporting = error_reporting();
            // error_reporting(0);
            //open the nfo file
            $m = new DOMDocument();
            $success = $m->load($nfoPath);
            if ($success == false) {
                //fail gracefully, since we will just use dummy information
                return false;
            }

            //parse the nfo file
            $t = getXmlTagValue($m, "title");
            //if the title is empty, use the filename like defined in the constructor
            $this->title = strlen($t) > 0 ? $t : $this->title;
            $this->plot = getXmlTagValue($m, "plot");
            if ($this->mediaType == Enumerations::MediaType_Movie) {
                $this->year = getXmlTagValue($m, "year");
            } else {
                $this->year = getXmlTagValue($m, "premiered");
            }
            $this->mpaa = getXmlTagValue($m, "mpaa");

            //specify a maximum number of actors to include
            $maxActorNumber = 4;
            $actorNodeList = $m->getElementsByTagName("actor");
            foreach ($actorNodeList as $actorNode) {
                if (count($this->actorList) > $maxActorNumber) {
                    break;
                }
                $actor = [];
                $nameItem = $actorNode->getElementsByTagName("name")->item(0);
                $actor["name"] = $nameItem != null ? $nameItem->nodeValue : "";
                $roleItem = $actorNode->getElementsByTagName("role")->item(0);
                $actor["role"] = $roleItem != null ? $roleItem->nodeValue : "";
                //if we have either an actor name or role, add this actor
                if ($actor["name"] != "" || $actor["role"] != "") {
                    $this->actorList[] = $actor;
                }
            }
        }
        //if made it to here, all is good. return true
        return true;
    }

    function getPosterPath() {
        return $this->getFullPathToContainingFolder() . "folder.jpg";
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
            Queries::insertVideo($this->title, $this->plot, $this->mpaa, $this->year, $this->fullPath, $this->getFiletype(), $this->mediaType, $this->getNfoLastModifiedDate(), $this->videoSourcePath, $this->videoSourceUrl);
        } else {
            //this is an existing video that needs to be updated. update it
            Queries::updateVideo($videoId, $this->title, $this->plot, $this->mpaa, $this->year, $this->fullPath, $this->getFiletype(), $this->mediaType, $this->getNfoLastModifiedDate(), $this->videoSourcePath, $this->videoSourceUrl);
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
        unset($this->posterExists);
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
        if ($this->hasMetadata()) {
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
