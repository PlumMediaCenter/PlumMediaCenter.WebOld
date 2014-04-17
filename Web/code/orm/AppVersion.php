<?php

namespace orm;

/**
 * @property String $version
 */
class AppVersion extends \ActiveRecord\Model {

    static $table_name = "app_version";
    static $primary_key = "version";

}
