<?php

require_once(dirname(__FILE__) . "/../config.php");
require_once(dirname(__FILE__) . "/Security.class.php");

/**
 * This is a Singleton class. You may only get an instance of this db class by calling getInstance()
 */
class DbManager
{

    private static $instance;
    private $host;
    private $userId;
    private $password;
    private $dbName;
    private $pdo;

    private function __construct()
    {
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
     * @param string $userId - the username to connect with
     * @param string $password - the password to connect with
     * @param string $dbName - the name of the database
     * @return \PDO
     */
    private static function getPdoInstance($host, $userId, $password, $dbName = null)
    {
        $dbNameParam = ($dbName == null) ? "" : ";dbname=$dbName";
        return new PDO("mysql:host=$host" . $dbNameParam, $userId, $password, array(
            PDO::ATTR_PERSISTENT => true
        ));
    }

    /**
     * Returns the singleton instance of the DatabaseManager
     * @return DatabaseManager - DatabaseManager Singleton
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new DbManager();
        }
        return self::$instance;
    }

    /**
     * Returns an instance of a PDO. If no parameters are supplied, this will return the 
     * singleton pdo created using the config settings
     * @param string $host - the host that the database is running on
     * @param string $userId - the username to connect with
     * @param string $password - the password to connect with
     * @param string $dbName - the name of the database
     * @return PDO
     */
    public static function GetPdo($host = null, $userId = null, $password = null, $dbName = null)
    {
        if ($host == null || $userId == null) {
            //if the parameters were provided, return a one time pdo that is not the singleton
            if (!self::$instance) {
                self::$instance = new DbManager();
            }
            return self::$instance->pdo;
        } else {
            return DbManager::getPdoInstance($host, $userId, $password, $dbName);
        }
    }

    /**
     * Determines if a database table exists.
     * @param string $tableName
     * @param string $host - the host that the database is running on
     * @param string $userId - the username to connect with
     * @param string $password - the password to connect with
     * @param string $dbName - the name of the database
     * @return boolean - true if the table exists, false if the table does not exist
     */
    public static function TableExists($tableName, $host = null, $userId = null, $password = null, $dbName = null)
    {
        try {
            $results = DbManager::query("show tables like '$tableName'", $host, $userId, $password, $dbName);
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
     * @param type $sql
     * @param args  - any additional arguments passed to this function will be bound to the statement
     */
    public static function NonQuery($sql)
    {
        $pdo = DbManager::getPdo();
        $stmt = $pdo->prepare($sql);
        $args = func_get_args();
        //remove the first argument, which is the $sql stmt
        array_shift($args);
        $success = $stmt->execute($args);

        //if the stmt failed execution, exit failure
        if ($success === false) {
            return false;
        } else {
            return true;
        }
    }

    public static function QueryManyObject($sql)
    {
        $pdo = DbManager::getPdo();
        $stmt = $pdo->prepare($sql);
        $args = func_get_args();
        //remove the first argument, which is the $sql stmt
        array_shift($args);
        $stmt->execute($args);
        return DbManager::FetchAllClass($stmt);
    }

    /**
     * Assume that each row is a class. return the results as an array of classes
     * @param type $sql
     * @param args  - any additional arguments passed to this function will be bound to the statement
     * @return type
     */
    public static function GetAllClassQuery($sql, $recordCount = null)
    {
        $pdo = DbManager::getPdo();
        $stmt = $pdo->prepare($sql);
        $args = func_get_args();
        //remove the first argument, which is the $sql stmt
        array_shift($args);
        $stmt->execute($args);
        return DbManager::FetchAllClass($stmt, $recordCount);
    }

    public static function Query($sql, $host = null, $userId = null, $password = null, $dbName = null)
    {
        $pdo = DbManager::getPdo($host, $userId, $password, $dbName);
        //if the pdo object could not be found, cancel the query gracefully
        if ($pdo == null) {
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

    /**
     * @param type $sql
     * @param args  - any additional arguments passed to this function will be bound to the statement
     */
    public static function SingleColumnQuery($sql)
    {
        $pdo = DbManager::getPdo();
        $stmt = $pdo->prepare($sql);
        $args = func_get_args();
        //remove the first argument, which is the $sql stmt
        array_shift($args);
        $stmt->execute($args);

        $result = DbManager::FetchAllClass($stmt);
        if ($result === false) {
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
    public static function QueryGetSingleRow($sql, $host = null, $userId = null, $password = null, $dbName = null)
    {
        $results = DbManager::query($sql, $host, $userId, $password, $dbName);
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
    public static function FetchAllAssociative($stmt)
    {
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
    public static function FetchAllClass($stmt, $recordCount = null)
    {
        if (!$recordCount) {
            $result = $stmt->fetchAll(PDO::FETCH_CLASS);
            return $result;
        } else {
            $result = new SplFixedArray($recordCount);
            $i = 0;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
                $result[$i++] = (object) $row;
            }
            return $result;
        }
    }

    public static function FetchAllColumn($stmt, $colNum)
    {
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN, $colNum);
        return $result;
    }

    public static function GetSingleItem($sql, $host = null, $userId = null, $password = null, $dbName = null)
    {
        $result = DbManager::query($sql, $host, $userId, $password, $dbName);
        if ($result !== null && count($result) > 0) {
            $result = $result[0];
            foreach ($result as $key => $item) {
                return $item;
            }
        } else {
            return null;
        }
    }

    public static function fetchSingleItem($stmt)
    {
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        if (count($result) === 1) {
            return $result[0];
        } else {
            return false;
        }
    }

    /**
     * Writes an object to the specified table. Assumes that each key in object is tableName 
     * @param type $tableName
     * @param type $keyName
     * @param type $keyValue
     * @param type $object
     * @return boolean
     */
    public static function WriteObjectToTable($tableName, $keyName, $object)
    {
        $sql = "update $tableName set ";
        $comma = '';
        foreach ($object as $key => $value) {
            $sql = "$sql $comma $key=:$value";
            $comma = ',';
        }
        //if no properties are actually being updated, fail
        if ($comma === '') {
            return false;
        }
        $sql .= " where $keyName = :key";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":key", $object[$keyName]);
        foreach ($object as $key => $value) {
            $stmt->bindParam(":$key", $value);
        }
        return $stmt->execute();
    }

    /**
     * Generates a string ready to be used in an 'in' statement for sql
     * @param type $list
     * @return type
     */
    public static function GenerateInStatement($list, $wrapInQuotes = true)
    {
        return DbManager::InOrNotIn($list, true, true, $wrapInQuotes);
    }

    /**
     * Generates a string combining all elements in the list 
     * @param type $list
     * @param type $wrapInQuotes
     * @param type $inLength
     * @return boolean|string - false if failure, the in stmt if success
     */
    public static function GenerateNotInStatement($list, $wrapInQuotes = null, $inLength = 1000)
    {
        return DbManager::InOrNotIn($list, false, false, $wrapInQuotes, $inLength);
    }

    public static function InOrNotIn($list, $useInInsteadOfNotIn = true, $useOrInsteadOfAnd = true, $wrapInQuotes = false, $inLength = 1000)
    {
        if (count($list) == 0) {
            return false;
        }
        $inNotIn = $useInInsteadOfNotIn ? ' in ' : ' not in ';
        $andOr = $useOrInsteadOfAnd ? ' or ' : ' and ';
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
        $actualAndOr = "";
        foreach ($lists as $sizedList) {
            if (count($sizedList) > 0) {
                //join all of the items together into an IN statement
                $s .= "$actualAndOr $inNotIn($q" . implode("$q,$q", $sizedList) . "$q)";
                $actualInNotIn = $inNotIn;
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
    public static function InterpolateQuery($query, $params)
    {
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
