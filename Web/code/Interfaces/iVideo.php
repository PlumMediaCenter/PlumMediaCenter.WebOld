<?php

/**
 *
 * @author bplumb
 */
interface iVideo {
    
    /*
     * Get the media type of the video
     * @return string - the media type of the video
     */
    function mediaType();

    /**
     * Get the videoId from the database of this video
     * @return int - the video id of this video in the database
     */
    function videoId();

    /**
     * Get the title of this video
     * @return string - the title of this video
     */
    function title();

    /**
     * Get the plot of this video
     * @return string - the plot of this video
     */
    function plot();

    /**
     * Get the mpaa rating of this video
     * @return string - the mpaa rating of this video
     */
    function mpaa();

    /**
     * Get the date the video was originally released
     * @return \DateTime - the date the video was originally released
     */
    function releaseDate();

    /**
     * Get the running time (in seconds) of the video
     * @return int - the running time (in seconds) of the video
     */
    function runningTimeSeconds();

    /**
     * Get the path to the video file
     * @return string - the path to the video file
     */
    function path();

    /**
     * Get the source path to the video file
     * @return string - the source path to the video file
     */
    function sourcePath();

    /**
     * Get the source url to the video file
     * @return string - the source url to the video file
     */
    function sourceUrl();

    /**
     * Gets whether the metadata for this video was loaded from an nfo file or not.
     * @return boolean - whether the metadata for this video was loaded from an nfo file or not.
     */
    function metadataLoadedFromNfo();

    /**
     * Get the list of genres for this video
     * @return string[] - the list of genres for this video
     */
    function genres();
    
    
}
