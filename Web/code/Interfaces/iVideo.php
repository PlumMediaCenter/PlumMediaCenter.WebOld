<?php

/**
 *
 * @author bplumb
 */
interface iVideo {
    
    function title();

    function plot();

    function mpaa();
    
    function path();

    function sourcePath();
    
    function sourceUrl();
    
    function metadataLoadedFromNfo();
    
    
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
