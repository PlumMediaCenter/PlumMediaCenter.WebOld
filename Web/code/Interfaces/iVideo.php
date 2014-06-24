<?php

/**
 *
 * @author bplumb
 */
interface iVideo {

    /**
     * Get the videoId from the database of this video
     * @return int - the video id of this video in the database
     */
    function getVideoId();

    function setVideoId($videoId);

    /**
     * Get the media type of the video
     * @return string - the media type of the video
     */
    function getMediaType();

    function setMediaType($mediaType);

    /**
     * Get the title of this video
     * @return string - the title of this video
     */
    function getTitle();

    function setTitle($title);

    /**
     * Get the plot of this video
     * @return string - the plot of this video
     */
    function getPlot();

    function setPlot($plot);

    /**
     * Get the mpaa rating of this video
     * @return string - the mpaa rating of this video
     */
    function getMpaa();

    function setMpaa($mpaa);

    /**
     * Get the date the video was originally released
     * @return \DateTime - the date the video was originally released
     */
    function getReleaseDate();

    function setReleaseDate($releaseDate);

    /**
     * Get the running time (in seconds) of the video
     * @return int - the running time (in seconds) of the video
     */
    function getRunningTimeSeconds();

    function setRunningTimeSeconds($runningTimeSeconds);

    /**
     * Get the path to the video file
     * @return string - the path to the video file
     */
    function getPath();

    function setPath($path);

    /**
     * Get the source path to the video file
     * @return string - the source path to the video file
     */
    function getSourcePath();

    function setSourcePath($sourcePath);

    /**
     * Get the source url to the video file
     * @return string - the source url to the video file
     */
    function getSourceUrl();

    function setSourceUrl($sourceUrl);
    /**
     * Gets whether the metadata for this video was loaded from an nfo file or not.
     * @return boolean - whether the metadata for this video was loaded from an nfo file or not.
     */
    //function metadataLoadedFromNfo();

    /**
     * Get the list of genres for this video
     * @return string[] - the list of genres for this video
     */
    function getGenres();

    /**
     * Sets the list of genres
     * @param string[] $genres
     */
    function setGenres($genres);
}
