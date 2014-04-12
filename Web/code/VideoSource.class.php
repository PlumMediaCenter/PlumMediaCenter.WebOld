<?php

class VideoSource {


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
    
    public static function Count(){
        return orm\VideoSource::count();
    }
    
    /**
     * Gets a list of VideoSource objects that fit the specified type
     * @param Enumerations\MediaType $mediaType
     */
    public static function GetByType($mediaType = null){
        if($mediaType === null){
            $sources = orm\VideoSource::all();
        }else{
            $sources = orm\VideoSource::find_all_by_mediaType($mediaType);
        }
        return $sources != null? $sources: array();
    }

}
