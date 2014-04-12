<?php

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
    private $sourceUrl;
    private $sourcePath;
    public $path;
    public $url;
    public $posterPath;
    public $posterUrl;

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

        $this->posterPath = $this->getExistingPosterPath();
        $this->posterUrl = $this->getPosterUrl();
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
            $posterFilePath = $basePath . $posterFilename;
            if (file_exists($posterFilePath) === true) {
                return $posterFilename;
            }
        }
        return null;
    }

    /**
     * Get the full path to the parent folder of this video
     * @return string 
     */
    protected function getContainingFolderPath() {
        $containingFolderPath = dirname($this->path) . "/";
        return $containingFolderPath;
    }

    /**
     * Returns the filename of the file provided to the video
     * @return string - the filename of the file provided to the video
     */
    protected abstract function getFilename();

    /**
     * Returns an array of possible names of poster files
     * @return string - an array of possible allowed filenames of posters for this video, in 
     *                  priority order from highest priority to lowest priority.
     */
    protected abstract function getPossiblePosterFilenames();

    /**
     * Gets the url to the poster for this video. This will ALWAYS return a url. So if 
     * this video does not have a poster, the url returned will point to the blank poster.
     * @return string - the url to the poster for this video. 
     */
    protected abstract function getPosterUrl();

    /**
     * Gets the full url to the parent folder of this video. 
     * @return string - the full url to the parent folder of this video
     */
    protected abstract function getContainingFolderUrl();

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
        $url = fileUrl(__FILE__) . "/../Content/Images/posters/";
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

    /**
     * Loads the metadata into memory. First checks to see if there is an NFO file in the normal places.
     * First will check to see if an nfo file of the same name as the video exists.
     * If not, then it will check for ANY nfo file, and use the first one it finds.
     * If not, then the video will check the online db and retrieve any metadata from there. 
     */
    public function loadMetadata() {

        $nfoPath = $this->getExistingNfoPath();
        //no nfo file was found. look online for the metadata
        if ($nfoPath === null) {
            $metadataFetcher = $this->getMetadataFetcher();
        } else {
            $nfoReader = $this->getNfoReader();
        }
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
        $sameNameNfoPath = $containingFolderPath . "$filename.nfo";
        if (file_exists($sameNameNfoPath) === true) {
            $nfoPath - $sameNameNfoPath;
        } else {//look for ANY nfo file in the folder.
            $files = glob("$containingFolderPath*.nfo");
            foreach ($files as $nfoFilePath) {
                $nfoPath = $nfoFilePath;
                break;
            }
        }
        return $nfoPath;
    }

}
