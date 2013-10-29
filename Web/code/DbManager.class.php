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
     * Determines if a database table exists.
     * @param string $tableName
     * @return boolean - true if the table exists, false if the table does not exist
     */
    public static function TableExists($tableName) {
        $results = DbManager::query("show tables like '$tableName'");
        if (count($results) > 0) {
            return true;
        } else {
            return false;
        }
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
     * @return array - the array of results, or an empty array if no results were found
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

    public static function singleColumnQuery($sql) {
        $result = DbManager::query($sql);
        if ($result == false) {
            return false;
        } else {
            $results = [];
            foreach ($result as $r) {
                foreach ($r as $key => $col) {
                    $results[] = $col;
                    break;
                }
            }
            return $results;
        }
        return false;
    }

    /**
     * Execute an sql statement without getting a return value
     * @param string $sql - the query to execute
     * @return array|boolean - the first and only row in a single row query, or false if rownum <> 1
     */
    public static function queryGetSingleRow($sql) {
        $results = DbManager::query($sql);
        if (count($results) === 1) {
            return $results[0];
        } else {
            return false;
        }
    }

    /**
     * Fetches all values into an array of associatiative arrays
     * @param obj $stmt - the pdo handler for the statement. This MUST have already been executed
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
     * @param obj $stmt -the pdo handler for the statement. This MUST have already been executed
     * @return array of arrays
     */
    public static function fetchAllClass($stmt) {
        $result = $stmt->fetchAll(PDO::FETCH_CLASS);
        return $result;
    }

    public static function fetchAllColumn($stmt, $colNum) {
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN, $colNum);
        return $result;
    }

    public static function GetSingleItem($sql) {
        $result = DbManager::query($sql);
        if ($result !== null && count($result) > 0) {
            return $result[0];
        } else {
            return null;
        }
    }

    public static function fetchSingleItem($stmt) {
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        if (count($result) === 1) {
            return $result[0];
        } else {
            return false;
        }
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

    /**
     * Generates a string combining all elements in the list 
     * @param type $list
     * @param type $wrapInQuotes
     * @param type $inLength
     */
    public static function NotIn($list, $wrapInQuotes = null, $inLength = 1000) {
        $q = $wrapInQuotes == true ? "'" : "";
        $lists = [];
        $listIndex = 0;
        $count = 0;
        //split the list into smaller chunks the size of inLength
        foreach ($list as $item) {
            $lists[$listIndex][] = $item;
            $count++;
            if ($count >= $inLength) {
                $listIndex++;
                $count = 0;
            }
        }

        $s = "";
        $and = "";
        foreach ($lists as $sizedList) {
            if (count($sizedList) > 0) {
                //join all of the items together into an IN statement
                $s .= "$and not in($q" . implode("$q,$q", $sizedList) . "$q)";
                $and = " and";
            }
        }
        return "$s ";
    }

}

?>
