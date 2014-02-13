<?php

class Security {

    /**
     * Redirects the user to the setup page. 
     */
    static function RedirectToSetup() {
        header("Location: Setup.php");
    }

    static function HandleLogin() {
        
    }

    static function GetUsername() {
        return config::$globalUsername;
    }

    static function DatabaseIsUpToDate() {
        include_once(dirname(__FILE__) . "/database/CreateDatabase.class.php");
        return CreateDatabase::DatabaseIsUpToDate();
    }

}

?>
