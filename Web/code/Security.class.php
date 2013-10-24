<?php
include_once(dirname(__FILE__) . "/../config.php");
class Security {

    static function GetUsername() {
        return config::$globalUsername;
    }

}

?>
