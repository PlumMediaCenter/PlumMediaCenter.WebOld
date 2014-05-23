<?php

namespace orm;

/**
 * @property String $filetype
 * @property String $hd_poster_url
 * @property Enumeration_MediaType $media_type
 * @property DateTime $metadata_modified_date 
 * @property String $mpaa
 * @property String $path
 * @property String $plot
 * @property DateTime $poster_modified_date
 * @property DateTime $release_date
 * @property int $running_time_seconds
 * @property String $sd_poster_url
 * @property String $title
 * @property String $url
 * @property int $video_id
 * @property String $video_source_path
 * @property String $video_source_url
 * @property String $metadata_loaded_from_nfo
 * @property String $poster_loaded_from_file_system
 */
class Video extends \ActiveRecord\Model {

    static $table_name = "video";
    static $primary_key = "video_id";

}
