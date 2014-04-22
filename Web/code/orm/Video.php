<?php

namespace orm;

/**
 * @property String $filetype
 * @property String $hdPosterUrl
 * @property Enumeration_MediaType $mediaType
 * @property DateTime $metadataLastModifiedDate 
 * @property String $mpaa
 * @property String $path
 * @property String $plot
 * @property DateTime $posterLastModifiedDate
 * @property DateTime $releaseDate
 * @property int $runningTimeSeconds
 * @property String $sdPosterUrl
 * @property String $title
 * @property String $url
 * @property int $videoId
 * @property String $videoSourcePath
 * @property String $videoSourceUrl
 * @property String $metadataLoadedFromNfo
 */
class Video extends \ActiveRecord\Model {

    static $table_name = "video";
    static $primary_key = "video_id";
    static $alias_attribute = array(
        'hdPosterUrl' => 'hd_poster_url',
        'mediaType' => 'media_type',
        'metadataLastModifiedDate' => 'metadata_last_modified_date',
        'posterLastModifiedDate' => 'poster_last_modified_date',
        'releaseDate' => 'release_date',
        'runningTimeSeconds' => 'running_time_seconds',
        'sdPosterUrl' => 'sd_poster_url',
        'videoId' => 'video_id',
        'videoSourcePath' => 'video_source_path',
        'videoSourceUrl' => 'video_source_url',
        'metadataLoadedFromNfo' => 'metadata_loaded_from_nfo'

    );
}
