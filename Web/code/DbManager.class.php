<?php

require_once(dirname(__FILE__) . "/../config.php");
require_once(dirname(__FILE__) . "/Security.class.php");

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
        try {
            $this->pdo = DbManager::getPdoInstance($this->host, $this->username, $this->password, $this->dbName);
        } catch (Exception $e) {
            
        }
    }

    /**
     * Returns a new instance of a PDO. 
     * @param string $host - the host that the database is running on
     * @param string $username - the username to connect with
     * @param string $password - the password to connect with
     * @param string $dbName - the name of the database
     * @return \PDO
     */
    private static function getPdoInstance($host, $username, $password, $dbName = null) {
        $dbNameParam = ($dbName == null) ? "" : ";dbname=$dbName";
        return new PDO("mysql:host=$host" . $dbNameParam, $username, $password, array(
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
     * Returns an instance of a PDO. If no parameters are supplied, this will return the 
     * singleton pdo created using the config settings
     * @param string $host - the host that the database is running on
     * @param string $username - the username to connect with
     * @param string $password - the password to connect with
     * @param string $dbName - the name of the database
     * @return PDO
     */
    public static function getPdo($host = null, $username = null, $password = null, $dbName = null) {
        if ($host == null || $username == null) {
            //if the parameters were provided, return a one time pdo that is not the singleton
            if (!self::$instance) {
                self::$instance = new DbManager();
            }
            return self::$instance->pdo;
        } else {
            return DbManager::getPdoInstance($host, $username, $password, $dbName);
        }
    }

    /**
     * Determines if a database table exists.
     * @param string $tableName
     * @param string $host - the host that the database is running on
     * @param string $username - the username to connect with
     * @param string $password - the password to connect with
     * @param string $dbName - the name of the database
     * @return boolean - true if the table exists, false if the table does not exist
     */
    public static function TableExists($tableName, $host = null, $username = null, $password = null, $dbName = null) {
        try {
            $results = DbManager::query("show tables like '$tableName'", $host, $username, $password, $dbName);
            if (count($results) > 0) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Execute an sql statement without getting a return value
     * @param string $sql - the query to execute
     * @return boolean success or failure
     */
    public static function NonQuery($sql, $host = null, $username = null, $password = null, $dbName = null) {
        $pdo = DbManager::getPdo($host, $username, $password, $dbName);
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute();

        //if the stmt failed execution, exit failure
        if ($success === false) {
            return false;
        } else {
            return true;
        }
    }

    public static function query($sql, $host = null, $username = null, $password = null, $dbName = null) {
        $pdo = DbManager::getPdo($host, $username, $password, $dbName);
        //if the pdo object could not be found, cancel the query gracefully
        if ($pdo == null) {
            writeToLog("Could not load the pdo object using host=$host username=$username password=***** dbName = $dbName");
            return [];
        }
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute();
        //if the stmt failed execution, exit failure
        if ($success === true) {
            return DbManager::FetchAllClass($stmt);
        } else {
            return [];
        }
    }

    public static function singleColumnQuery($sql, $host = null, $username = null, $password = null, $dbName = null) {
        $result = DbManager::query($sql, $host, $username, $password, $dbName);
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
    public static function queryGetSingleRow($sql, $host = null, $username = null, $password = null, $dbName = null) {
        $results = DbManager::query($sql, $host, $username, $password, $dbName);
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
    public static function FetchAllAssociative($stmt) {
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
    public static function FetchAllClass($stmt) {
        $result = $stmt->fetchAll(PDO::FETCH_CLASS);
        return $result;
    }

    public static function FetchAllColumn($stmt, $colNum) {
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN, $colNum);
        return $result;
    }

    public static function GetSingleItem($sql, $host = null, $username = null, $password = null, $dbName = null) {
        $result = DbManager::query($sql, $host, $username, $password, $dbName);
        if ($result !== null && count($result) > 0) {
            $result = $result[0];
            foreach ($result as $key => $item) {
                return $item;
            }
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
    public static function GenerateInStatement($list, $wrapEachWithQuotes = true) {
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
     * Generates a like statement for each item in the list
     * @param type $list
     * @param type $dbCol
     * @param type $logicalOperator
     * @return type
     */
    public static function GenerateLikeStatement($list, $dbCol, $logicalOperator = "or") {
        $stmt = "";
        $logic = "";
        foreach ($list as $item) {
            $stmt .= "$logic $dbCol like '%$item%'";
            $logic = $logicalOperator;
        }
        return $stmt;
    }

    /**
     * Generates a string combining all elements in the list 
     * @param type $list
     * @param type $wrapInQuotes
     * @param type $inLength
     * @return boolean|string - false if failure, the in stmt if success
     */
    public static function NotIn($list, $wrapInQuotes = null, $inLength = 1000) {
        if (count($list) == 0) {
            return false;
        }
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

    /**
     * Replaces any parameter placeholders in a query with the value of that
     * parameter. Useful for debugging. Assumes anonymous parameters from 
     * $params are are in the same order as specified in $query
     *
     * @param string $query The sql query with parameter placeholders
     * @param array $params The array of substitution parameters
     * @return string The interpolated query
     */
    public static function InterpolateQuery($query, $params) {
        $keys = array();
        $values = $params;

        # build a regular expression for each parameter
        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $keys[] = '/:' . $key . '/';
            } else {
                $keys[] = '/[?]/';
            }

            if (is_array($value))
                $values[$key] = implode(',', $value);

            if (is_null($value))
                $values[$key] = 'NULL';
        }
        // Walk the array to see if we can add single-quotes to strings
        array_walk($values, create_function('&$v, $k', 'if (!is_numeric($v) && $v!="NULL") $v = "\'".$v."\'";'));

        $query = preg_replace($keys, $values, $query, 1, $count);

        return $query;
    }

}

?>
