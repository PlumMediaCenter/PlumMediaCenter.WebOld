<?php

/**
 *
 * @author bplumb
 */
interface iVideo {
    
    function videoId();
    
    function title();

    function plot();

    function mpaa();
    
    function path();

    function sourcePath();
    
    function sourceUrl();
    
    function metadataLoadedFromNfo();
    
    /**
     * The path to the poster or the url to the poster
     */
    function poster();
    
    
    /**
     * The list of genres for this video
     * @return string[] - a list of the genres for this video
     */
    function genres();

    /**
     * The date the video was originally released
     * @return \DateTime
     */
    function releaseDate();
    
    /**
     * The running time of the video in seconds
     */
    function runningTimeSeconds();

}
