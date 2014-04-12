<?php

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
        //retrieve the poster path if the video has a poster in its folder with it
        $this->posterPath = $this->getExistingPosterPath();
    }

    /**
     * Returns the filename of the file provided to the video
     * @return string - the filename of the file provided to the video
     */
    protected function getFilename() {
        return pathinfo($this->path, PATHINFO_FILENAME);
    }

    /**


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
            "$containingFolderPath/$filename.jpg",
            "$containingFolderPath/$filename.png",
            "$containingFolderPath/poster.jpg",
            "$containingFolderPath/poster.png",
            "$containingFolderPath/folder.png"
        );
        return $posterFilenames;
    }

    /**
     * Gets the full url to the parent folder of this video. 
     * @return string - the full url to the parent folder of this video
     */
    protected function getContainingFolderUrl() {
        $containingFolderUrl = dirname($this->url);
        return $containingFolderUrl;
    }

    /**
     * Gets the url to the poster for this video. This will ALWAYS return a url. So if 
     * this video does not have a poster, the url returned will point to the blank poster.
     * @return string - the url to the poster for this video. 
     */
    protected function getPosterUrl() {
        $posterFilename = $posterUrl = null;
        //if no poster exists
        if ($posterFilename === null) {
            $posterUrl = $this->getBlankPosterBaseUrl() . $this->getBlankPosterName();
        } else {
            $containingFolderUrl = $this->getContainingFolderUrl();

            $posterUrl = $containingFolderUrl . $posterFilename;
        }
    }

    /**
     * Retrieves the name of the blank poster that will be used if no poster was found for this video
     */
    protected function getBlankPosterName() {
        return "BlankPoster.jpg";
    }

    function getNfoReader() {
        if ($this->nfoReader == null) {
            $this->nfoReader = new MovieNfoReader();
        }
        return $this->nfoReader;
    }

    /**
     * Returns a Video Metadata Fetcher. Search by title
     * @return MovieMetadataFetcher
     */
    protected function getMetadataFetcher() {
        include_once(dirname(__FILE__) . "/MetadataFetcher/MovieMetadataFetcher.class.php");
        $metadataFetcher = $this->getMetadataFetcherClass();
        $foldername = pathinfo(dirname($this->path), PATHINFO_FILENAME);
        $metadataFetcher->searchByTitle($foldername);
        return $metadataFetcher;
    }

}
