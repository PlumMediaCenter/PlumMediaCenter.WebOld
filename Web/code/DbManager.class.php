<?php

require_once(dirname(__FILE__) . "/../config.php");

/**
 * This is a Singleton class. You may only get an instance of this db class by calling getInstance()
 */
class DbManager {

    private static $instance;
    private $host;
    private $username;
    private $password;
    private $dbName;
    private $pdo;

    private function __construct() {
        $this->host = config::$dbHost;
        $this->username = config::$dbUsername;
        $this->password = config::$dbPassword;
        $this->dbName = config::$dbName;

        $this->pdo = new PDO("mysql:host=$this->host;dbname=$this->dbName", $this->username, $this->password, array(
            PDO::ATTR_PERSISTENT => true
        ));
    }

    /**
     * Returns the singleton instance of the DatabaseManager
     * @return DatabaseManager - DatabaseManager Singleton
     */
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new DbManager();
        }
        return self::$instance;
    }

    /**
     * Returns the pdo of the singleton database manager
     */
    public static function getPdo() {
        if (!self::$instance) {
            self::$instance = new DbManager();
        }
        return self::$instance->pdo;
    }

}

?>
