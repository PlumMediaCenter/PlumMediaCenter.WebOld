<?php

include_once(dirname(__FILE__) . '/../../lib/php-mp4info/MP4Info.php');

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FilesystemVideo
 *
 * @author bplumb
 */
abstract class FileSystemVideo {

    protected $posterFilenames;
    protected $mediaType;
    protected $metadataLoaded = false;
    protected $posterPath;
    protected $posterUrl;
    protected $nfoReader = null;

    /** Database Fields */
    protected $title;
    protected $plot;
    protected $mpaa;
    protected $releaseDate;
    protected $genres;
    protected $sourceUrl;
    protected $sourcePath;
    protected $path;
    protected $url;

    function __construct($path, $sourcePath, $sourceUrl) {
        //save the sourceUrl
        $this->sourceUrl = $sourceUrl;
        //save the sourcePath
        $this->sourcePath = str_replace("\\", "/", realpath($sourcePath)) . "/";
        //determine the full path
        $fullPathRealPath = realpath($path);
        if ($fullPathRealPath === false) {
            throw new Exception("Unable to construct a video object at path $fullPath: path does not exist");
        }
        //save the full path
        $this->path = str_replace("\\", "/", $fullPathRealPath);

        //if this video does not exist, throw a new exception
        if (file_exists($this->path) === false) {
            throw new Exception("Video does not exist at path $this->path");
        }

        //generate the video file url
        $relativePath = str_replace($this->sourcePath, "", $this->path);
        $this->url = $this->sourceUrl . $relativePath;

        //retrieve the poster path if the video has a poster in its folder with it
        $this->posterPath = $this->getExistingPosterPath();

        $this->posterUrl = $this->getPosterUrl();
    }

    /**
     * Getter for the $path property
     * @return string - the path to the video
     */
    function getPath() {
        return $this->path;
    }

    /**
     * Returns the full path to the existing poster, if this video has a poster.
     * This function searches through the list of possible poster filenames until it finds
     * one that matches. If no matching poster was found, null is returned.
     * @return string|null - full path to poster if one of possible filenames was found, null if not found
     */
    protected function getExistingPosterPath() {
        $possiblePosterFilenames = $this->getPossiblePosterFilenames();
        $basePath = $this->getContainingFolderPath();
        foreach ($possiblePosterFilenames as $posterFilename) {
            $posterFilePath = "$basePath/$posterFilename";
            if (file_exists($posterFilePath) === true) {
                return $posterFilePath;
            }
        }
        return null;
    }

    /**
     * Get the full path to the parent folder of this video
     * @return string 
     */
    protected function getContainingFolderPath() {
        $containingFolderPath = dirname($this->path);
        return $containingFolderPath;
    }

    /**
     * Returns the filename of the file provided to the video
     * @return string - the filename of the file provided to the video
     */
    protected abstract function getFilename();

    /**
     *  Returns an array of possible names of poster files.
     *      Checks for files in this order:
     *      <filename>-poster.(jpg/png)
     *      poster.(jpg/png)
     *      folder.jpg
     * @return string - an array of possible allowed filenames of posters for this video, in 
     *                  priority order from highest priority to lowest priority.
     */
    protected function getPossiblePosterFilenames() {
        $containingFolderPath = $this->getContainingFolderPath();
        $filename = $this->getFilename();
        $posterFilenames = array(
            "$filename.jpg",
            "$filename.png",
            "poster.jpg",
            "poster.png",
            "folder.png",
            "folder.jpg"
        );
        return $posterFilenames;
    }

    /**
     * Gets the url to the poster for this video. This will ALWAYS return a url. So if 
     * this video does not have a poster, the url returned will point to the blank poster.
     * @return string - the url to the poster for this video. 
     */
    protected abstract function getPosterUrl();

    /**
     * Returns the full url to the video file
     * @return string - the full url to the video file
     */
    public abstract function getUrl();

    /**
     * Retrieves the name of the blank poster that will be used if no poster was found for this video
     */
    protected abstract function getBlankPosterName();

    /**
     * Forces each child class to load their corresponding metadata fetcher class
     */
    protected abstract function getMetadataFetcher();

    /**
     * Has the child object fetch its nfo reader class.
     */
    protected abstract function getNfoReader();

    /**
     * Returns the url to the folder that contains all of the blank posters
     * @return string - the url to the folder containing all of the blank posters
     */
    protected function getBlankPosterBaseUrl() {
        $url = fileUrl(__FILE__) . "/../Content/Images/posters/blankPosters";
        $url = url_remove_dot_segments($url);
        return FileSystemVideo::EncodeUrl($url);
    }

    /**
     * Replaces any invalid url characters with encoded url characters
     * @param string $url - the subject url to be encoded
     * @return string - the treated url
     */
    protected static function EncodeUrl($url) {
        return str_replace(" ", "%20", $url);
    }

    /* First checks to see if there is an NFO file in the normal places.
     * First will check to see if an nfo file of the same name as the video exists.
     * If not, then it will check for ANY nfo file, and use the first one it finds.
     * Returns the path to an existing nfo file, or null if one was not found.
     */

    public function getExistingNfoPath() {
        $nfoPath = null;
        //check to see if there is an nfo file with the same name as this video in the same directory.
        $filename = pathinfo($this->path, PATHINFO_FILENAME);
        $containingFolderPath = $this->getContainingFolderPath();
        $sameNameNfoPath = "$containingFolderPath/$filename.nfo";
        if (file_exists($sameNameNfoPath) === true) {
            $nfoPath = $sameNameNfoPath;
        } else {//look for ANY nfo file in the folder.
            $files = glob("$containingFolderPath/*.nfo");
            foreach ($files as $nfoFilePath) {
                $nfoPath = $nfoFilePath;
                break;
            }
        }
        return $nfoPath;
    }

    /**
     * Gets the full url to the parent folder of this video. 
     * @return string - the full url to the parent folder of this video
     */
    public function getContainingFolderUrl() {
        $containingFolderUrl = dirname($this->url);
        return $containingFolderUrl;
    }

    /**
     * Loads the metadata into memory. 
     * First will check to see if an nfo file of the same name as the video exists.
     * If not, then it will check for ANY nfo file, and use the first one it finds.
     * If not, then the video will check the online db and retrieve any metadata from there. 
     */
    public function loadMetadata() {

        $iVideoMetadataMetadata = null;

        $nfoPath = $this->getExistingNfoPath();
        //no nfo file was found. look online for the metadata
        if ($nfoPath === null) {
            $iVideoMetadataMetadata = $this->getMetadataFetcher();
        } else {
            $iVideoMetadataMetadata = $this->getNfoReader();
        }

        //extract all of the video information from the fetcher or reader
        $this->title = $iVideoMetadataMetadata->title();
        $this->plot = $iVideoMetadataMetadata->plot();
        $this->mpaa = $iVideoMetadataMetadata->mpaa();
        $this->releaseDate = $iVideoMetadataMetadata->releaseDate();
        $this->metadataRunningTimeSeconds = $iVideoMetadataMetadata->runningTimeSeconds();
        $this->metadataLoaded = true;

        $this->genres = $iVideoMetadataMetadata->genres();
    }

    /**
     * Parses the mp4 video's metadata to find the full length of the video in seconds. If the 
     * mp4 file was not able to be parsed, then the metadata length will be used instead. If
     * that is not able to be retrieved, then we will assume this video's length is 0 seconds
     * @return int|boolean - the number of seconds if successful, false if unsuccessful
     */
    private function getRunningTimeSeconds() {
        $result = 0;
        $fileRunningTime = null;
        //the mp4info class likes to spit out random crap. Hide it with an output buffer
        ob_start();
        $result = @MP4Info::getInfo($this->path);
        ob_end_clean();
        if ($result !== null && $result != false && $result->hasVideo === true) {
            $fileRunningTime = intval($result->duration);
        }
        //if the file runtime was able to be determined based on the file itself, use that.
        if ($fileRunningTime !== null) {
            $result = $fileRunningTime;
        } else {
            //if the metadata has not been loaded yet, load it now
            if ($this->metadataLoaded === false) {
                $this->loadMetadata();
            }
            if ($this->metadataRunningTimeSeconds === null) {
                return 0;
            } else {
                $result = $this->metadataRunningTimeSeconds;
            }
        }
        return $result;
    }

    /**
     * Gets the filetype (a.k.a. extension) of the video
     * @return string - the filetype (a.k.a. extension of the video)
     */
    public function getFiletype() {
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }

    /**
     * Gets the title of this video. If the video's metadata hasn't been fetched yet, it is fetched.
     * @return string
     */
    public function getTitle() {
        if ($this->metadataLoaded === false) {
            $this->loadMetadata();
        }
        return $this->title;
    }

    /**
     * Returns the date of the last time the nfo file was modified
     * @return \DateTime
     */
    public function getMetadataLastModifiedDate() {
        return $this->getModifiedDate($this->getExistingNfoPath());
    }

    /**
     * Returns the date of the last time the poster file was modified
     * @return \DateTime
     */
    public function getPosterLastModifiedDate() {
        return $this->getModifiedDate($this->getExistingPosterPath());
    }

    /**
     * Returns the date of the last time the file at the path in the parameter was modified
     * @param string $path - the full path to the file
     * @return \DateTime
     */
    private function getModifiedDate($path) {
        $modifiedDate = null;
        $filemtimeValue = filemtime($path);
        if ($filemtimeValue !== false) {
            $modifiedDate = new DateTime();
            $modifiedDate->setTimestamp($filemtimeValue);
        }
        return $modifiedDate;
    }

    function getSdPosterUrl() {
        return FileSystemVideo::EncodeUrl($this->getContainingFolderUrl() . "/folder.sd.jpg");
    }

    function getHdPosterUrl() {
        $hdPosterUrl = $this->getContainingFolderUrl() . "/folder.hd.jpg";
        return FileSystemVideo::EncodeUrl($hdPosterUrl);
    }

    /**
     * Saves this video to the database
     */
    public function save() {
        $v = new \orm\Video();
        $v->title = $this->title;
        $v->runningTimeSeconds = $this->getRunningTimeSeconds();
        $v->plot = $this->plot;
        $v->path = $this->path;
        $v->url = $this->getUrl();
        $v->filetype = $this->getFiletype();
        $v->metadataLastModifiedDate = $this->getMetadataLastModifiedDate();
        $v->posterLastModifiedDate = $this->getPosterLastModifiedDate();
        $v->mpaa = $this->mpaa;
        $v->releaseDate = $this->releaseDate;
        $v->mediaType = $this->mediaType;
        $v->videoSourcePath = $this->sourcePath;
        $v->videoSourceUrl = $this->sourceUrl;
        $v->sdPosterUrl = $this->getSdPosterUrl();
        $v->hdPosterUrl = $this->getHdPosterUrl();
        $v->save();

        //save each genre
        foreach ($this->genres as $genre) {
            //save this genre to this movie
            $vg = new \orm\VideoGenre();
            $vg->name = $genre;
            $vg->videoId = $v->id;
            $vg->save();
        }
    }

}
