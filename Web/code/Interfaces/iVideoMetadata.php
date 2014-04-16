<?php

interface iVideo {

    function title();

    function rating();

    function plot();

    function mpaa();

    function posterUrl();

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
