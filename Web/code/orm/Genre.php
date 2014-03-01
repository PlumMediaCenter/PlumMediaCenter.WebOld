<?php

namespace orm;

/**
 * @property String $genreName
 */
class Genre extends ActiveRecord\Model {

    static $table_name = "genre";
    static $primary_key = "genre_name";
    static $alias_attribute = array(
        'genreName' => 'genre_name'
    );

}
