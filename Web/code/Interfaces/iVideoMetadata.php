<?php

interface iVideoMetadata {
    function title();

    function rating();

    function plot();

    function mpaa();

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

    /**
     * A url to the poster for this video
     */
    function posterUrl();
}
