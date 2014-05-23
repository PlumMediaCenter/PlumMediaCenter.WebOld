<?php

class EmptyVideoMetadata implements iVideoMetadata {

    public function genres() {
        return [];
    }

    public function mpaa() {
        return FileSystemVideo::UNKNOWN_MPAA;
    }

    public function plot() {
        return null;
    }

    public function posterUrl() {
        return null;
    }

    public function rating() {
        
    }

    public function releaseDate() {
        
    }

    public function runningTimeSeconds() {
        
    }

    public function title() {
        
    }

}
