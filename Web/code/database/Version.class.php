<?php

include_once(dirname(__FILE__) . "/../DbManager.class.php");

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Version
 *
 * @author bplumb
 */
class Version {

    static function GetVersion($host, $username, $password, $dbName) {
        $version = DbManager::GetSingleItem("select * from app_version", $host, $username, $password, $dbName);
        return $version;
    }

}
