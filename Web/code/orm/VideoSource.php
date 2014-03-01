<?php

namespace orm;

/**
 * @property String $securityType
 * @property boolean $refreshVideos
 * @property Enumerations_MediaType $mediaType
 * @property String $location
 * @property String $baseUrl
 */
class VideoSource extends \ActiveRecord\Model {

    static $table_name = "video_source";
    static $primary_key = "location";
    static $alias_attribute = array(
        'securityType' => 'security_type',
        'baseUrl' => 'base_url',        
        'mediaType' => 'media_type',
        'refreshVideos' => 'refresh_videos'
    );

}
