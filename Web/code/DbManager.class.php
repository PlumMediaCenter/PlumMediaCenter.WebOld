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
     * @return PDO
     */
    public static function getPdo() {
        if (!self::$instance) {
            self::$instance = new DbManager();
        }
        return self::$instance->pdo;
    }

    /**
     * Execute an sql statement without getting a return value
     * @param string $sql - the query to execute
     * @return boolean success or failure
     */
    public static function nonQuery($sql) {
        $pdo = DbManager::getPdo();
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute();
        //if the stmt failed execution, exit failure
        if ($success === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Execute an sql statement without getting a return value
     * @param string $sql - the query to execute
     * @return boolean success or failure
     */
    public static function query($sql) {
        $pdo = DbManager::getPdo();
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute();
        //if the stmt failed execution, exit failure
        if ($success === true) {
            return DbManager::fetchAllClass($stmt);
        } else {
            return [];
        }
    }

    /**
     * Fetches all values into an array of associatiative arrays
     * @param obj $stmt - the pdo handler
     * @return array of arrays
     */
    public static function fetchAllAssociative($stmt) {
        $result = [];
        $val = $stmt->fetch(PDO::FETCH_ASSOC);
        while ($val != null) {
            $result[] = $val;
            $val = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $result;
    }

    /**
     * Fetches all values into an array of associatiative arrays
     * @param obj $stmt - the pdo handler
     * @return array of arrays
     */
    private static function fetchAllClass($stmt) {
        $result = [];
        $val = $stmt->fetch(PDO::FETCH_OBJ);
        while ($val != null) {
            $result[] = $val;
            $val = $stmt->fetch(PDO::FETCH_OBJ);
        }
        return $result;
    }

    /**
     * Generates a string ready to be used in an 'in' statement for sql
     * @param type $list
     * @return type
     */
    public static function generateInStatement($list, $wrapEachWithQuotes = true) {
        $pdo = DbManager::getPdo();
        $str = '';
        $notFirstTime = false;
        foreach ($list as $item) {
            if ($notFirstTime == true) {
                $str .= ",";
            }
            $quoted = $pdo->quote($item);
            if ($wrapEachWithQuotes === false) {
                $quoted = substr($quoted, 1, -1);
            }
            $str .=$quoted;
            $notFirstTime = true;
        }
        return $str;
    }

}

?>
