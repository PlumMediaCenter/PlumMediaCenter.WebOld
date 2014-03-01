<?php

namespace orm;

/**
 * @property int $id - the primary key for this table. use $genreName and $videoId to reference the item you want
 * @property String $genreName
 * @property int $videoId
 */
class VideoGenre extends ActiveRecord\Model {

    static $table_name = "video_genre";
    static $primary_key = "id";
    static $alias_attribute = array(
        'genreName' => 'genre_name',
        'videoId' => 'video_id'
    );

}
