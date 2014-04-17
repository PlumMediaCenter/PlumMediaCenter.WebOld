<?php

namespace orm;

/**
 * @property String $writer
 * @property int $tvShowVideoId
 * @property int $seasonNumber
 * @property int $episodeNumber
 * @property String $director
 */
class TvEpisode extends \ActiveRecord\Model {

    static $table_name = "tv_episode";
    static $primary_key = "video_id";
    static $alias_attribute = array(
        'videoId' => 'video_id',
        'tvShowVideoId' => 'tv_show_video_id',
        'seasonNumber' => 'season_number',
        'episodeNumber' => 'episode_number'
    );

}
