<?php

include_once(dirname(__FILE__) . '/FileSystemVideo.php');
include_once(dirname(__FILE__) . '/../../MetadataFetcher/MovieMetadataFetcher.class.php');
include_once(dirname(__FILE__) . '/../../NfoReader/MovieNfoReader.class.php');


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FileSystemMovie
 *
 * @author bplumb
 */
class FileSystemMovie extends FileSystemVideo {

    function __construct($videoSourceUrl, $videoSourcePath, $fullPath) {
        parent::__construct($videoSourceUrl, $videoSourcePath, $fullPath);
        //the media type of this video is movie
        $this->mediaType = Enumerations\MediaType::Movie;
    }

    /**
     * Returns the filename of the file provided to the video
     * @return string - the filename of the file provided to the video
     */
    protected function getFilename() {
        //if this video was found in the root of the video source, then use the filename. 
        //Otherwise, lets assume that the video is in a folder whose folder name is the 
        //name of the video
        $videoContainingFolder = dirname($this->path);
        if (strtoupper($videoContainingFolder) === strtoupper($this->sourcePath)) {
            return pathinfo($this->path, PATHINFO_FILENAME);
        } else {
            //return the name of the folder
            return pathinfo(dirname($this->path), PATHINFO_FILENAME);
        }
    }

    public function generateTextOnlyPoster() {
        return parent::generateTextOnlyPosterByType($this->mediaType);
    }

    /**
     * Returns the full url to the video file
     * @return string - the full url to the video file
     */
    public function getUrl() {
        return $this->getContainingFolderUrl() . "/" . pathinfo($this->path, PATHINFO_FILENAME) . "." . pathinfo($this->path, PATHINFO_EXTENSION);
    }

    /**

      /**
     * Gets the url to the poster for this video. This will ALWAYS return a url. So if 
     * this video does not have a poster, the url returned will point to the blank poster.
     * @return string - the url to the poster for this video. 
     */
//    protected function getPosterUrl() {
//        $posterUrl = null;
//        $posterFilename = ($this->posterPath !== null) ? pathinfo($this->posterPath, PATHINFO_FILENAME) . "." . pathinfo($this->posterPath, PATHINFO_EXTENSION) : null;
//        //if no poster exists, use a blank poster
//        if ($posterFilename === null) {
//            $blankPosterBaseUrl = $this->getBlankPosterBaseUrl();
//            $blankPosterName = $this->getBlankPosterName();
//            $posterUrl = "$blankPosterBaseUrl/$blankPosterName";
//        } else {
//            //use the poster that exists
//            $containingFolderUrl = $this->getContainingFolderUrl();
//            $posterUrl = "$containingFolderUrl/$posterFilename";
//        }
//        return $posterUrl;
//    }

    /**
     * Retrieves the name of the blank poster that will be used if no poster was found for this video
     */
    protected function getBlankPosterName() {
        return "BlankPoster.jpg";
    }

    protected function getNfoReader() {
        if (isset($this->nfoReader) === false) {
            $this->nfoReader = new MovieNfoReader();
            $this->nfoReader->loadFromFile($this->getExistingNfoPath());
        }
        return $this->nfoReader;
    }

    /**
     * Returns a Video Metadata Fetcher. Search by title
     * @return MovieMetadataFetcher
     */
    protected function getMetadataFetcher() {
        $metadataFetcher = new MovieMetadataFetcher();
        $foldername = pathinfo(dirname($this->path), PATHINFO_FILENAME);
        try {
            $metadataFetcher->searchByTitle($foldername);
        } catch (Exception $e) {
            //the metadata fetcher failed for some reason. return null
            return null;
        }
        return $metadataFetcher;
    }

}
