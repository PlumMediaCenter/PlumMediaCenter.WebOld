<?php

class VideoSource {

    /**
     * Adds a new video source to the vide_source table
     */
    public static function Add($location, $baseUrl, $mediaType, $securityType) {
        $loc = new orm\VideoSource();
        $loc->location = $location;
        $loc->baseUrl = $baseUrl;
        $loc->mediaType = $mediaType;
        $loc->securityType = $securityType;
        $success = $loc->save();
        return $success;
    }

    
    /**
     * Updates an existing video source in the database
     */
    public static function Update($originalLocation, $newLocation, $baseUrl, $mediaType, $securityType) {
        $loc = orm\VideoSource::find($originalLocation);
        $loc->location = $newLocation;
        $loc->baseUrl = $baseUrl;
        $loc->mediaType = $mediaType;
        $loc->securityType = $securityType;
        $loc->refreshVideos = true;
        $success = $loc->save();
        return $success;
    }

    /**
     * Deletes a video source from the video_source table
     * @param string $location - the location used as the primary key to identify the video source to delete
     * @return boolean - true if successful, false if failure
     */
    public static function Delete($location) {
        $loc = orm\VideoSource::find($location);
        $success = $loc->delete();
        return $success;
    }
    
    public static function GetAll(){
        return VideoSource::GetByType();
    }
    
    /**
     * Gets a list of VideoSource objects that fit the specified type
     * @param Enumerations\MediaType $mediaType
     */
    public static function GetByType($mediaType = null){
        if($mediaType === null){
            $sources = orm\VideoSource::all();
        }else{
            $sources = orm\VideoSource::find_by_mediaType($mediaType);
        }
        return $sources;
    }

}
