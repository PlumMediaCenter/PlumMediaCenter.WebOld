<?php

namespace orm;

/**
 * @property int $videoId
 * @property String $username
 * @property int $timeInSeconds
 * @property DateTime $dateWatched
 */
class WatchVideo extends ActiveRecord\Model {

    static $table_name = "watch_video";
    static $primary_key = "id";
    static $alias_attribute = array(
        'videoId' => 'security_type',
        'timeInSeconds' => 'time_in_seconds',
        'dateWatched' => 'date_watched'
    );

}
