<?php

class ExtendedPDO extends PDO
{

    function __construct($connectionString, $username = null, $password = null)
    {
        parent::__construct($connectionString, $username, $password, [PDO::ATTR_PERSISTENT => true]);
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Perform the query that returns no results, with parameter binding
     * @param string $query
     * @param object $parameters
     * @return bool - success indicator
     */
    public function execute($query, $parameters = null)
    {
        $stmt = $this->_prepare($query, $parameters);
        return $stmt->execute($parameters);
    }

    /**
     * Get a record by ID
     * @param string $tableName
     * @param int $id
     * @return type
     */
    function getById($tableName, $id)
    {
        return $this->getByIds($tableName, [$id])[0];
    }

    /**
     * Get all of the records with the given IDs from the specified table
     */
    function getByIds(string $tableName, $ids)
    {
        $params = ["ids" => $ids];
        return $this->getMany("
            select * from $tableName
            where id in :ids", $params);
    }

    /**
     * Get a single object from a query that should only return a single record
     * @param string $query
     * @return object
     * @throws Exception
     */
    function getOne($query, $params = [])
    {
        $rows = $this->getMany($query, $params);
        $count = count($rows);
        if ($count === 0) {
            throw new Exception('Zero rows were retrieved');
        } elseif ($count > 1) {
            throw new Exception('Multiple rows were retrieved');
        } else {
            return $rows[0];
        }
    }

    /**
     * Get an array of objects for the given query
     * @param string $query
     * @return object[]
     */
    function getMany($query, $params = [])
    {
        $stmt = $this->_prepare($query, $params);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    private function _prepare(&$query, &$params = [])
    {
        $arrayParams = [];
        $nonArrayParams = [];
        $hasArrays = false;
        //if any of the parameters is an array, generate an in statement for arrays
        foreach ($params as $key => $param) {
            if (is_array($param)) {
                $hasArrays = true;
                $arrayParams[$key] = $param;
            } else {
                $nonArrayParams[$key] = $param;
            }
        }
        if ($hasArrays) {
            //generate an in statement for each of the array params
            foreach ($arrayParams as $paramName => $array) {
                $match = null;
                preg_match("/((?:[a-zA-Z_][a-zA-Z0-9_]*\.+)*[a-zA-Z_][a-zA-Z0-9_]*)\s+(in|not\s+in)\s+:($paramName)/", $query, $match, PREG_OFFSET_CAPTURE);
                $startIndex = $match[0][1];
                $length = strlen($match[0][0]);
                $columnName = $match[1][0];
                $inNotInStmt = strtolower(trim($match[2][0]));
                $isIn = $inNotInStmt === 'in';
                //generate the IN statement for this param
                $inStatement = $this->getInStatement($columnName, $array, $isIn);
                //replace the bound parameter with the in statement
                $query = substr($query, 0, $startIndex) . $inStatement . substr($query, $startIndex + $length);
                //remove this item from the params array
                unset($params[$paramName]);
            }
        }
        return $this->prepare($query);
    }

    private function getInStatement($columnName, $list, $isIn = false, $inLength = 2)
    {
        if (count($list) == 0) {
            return false;
        }
        //if the first element in the array is a string, wrap each item in quotes. Otherwise, assume non-string value
        $wrapInQuotes = is_string($list[0]);
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
        $s = '';
        $inNotIn = $isIn ? 'in' : 'not in';
        $andOr = '';
        foreach ($lists as $sizedList) {
            if (count($sizedList) > 0) {
                //join all of the items together into an IN statement
                $s .= "$andOr $columnName $inNotIn($q" . implode("$q,$q", $sizedList) . "$q) ";
                $andOr = $isIn === true ? 'or' : 'and';
            }
        }
        return " ($s) ";
    }

    /**
     * Get a single column value from a single row, or null if no results founds
     * @param string $query
     * @return object
     */
    function getValue($query, $params = null)
    {
        $row = $this->getOne($query, $params);
        if (count(array_keys((array) $row)) > 1) {
            throw new Exception('Cannot get value: multiple keys found');
        }
        foreach ($row as $value) {
            return $value;
        }
        return null;
    }

    /**
     * Insert a record
     * @param string $tableName
     * @param object $poco
     * @return object
     */
    function insert($tableName, &$poco)
    {
        $array = [$poco];
        return $this->insertMany($tableName, $array)[0];
    }

    /**
     * Insert many records
     * @param string $tableName
     * @param type $pocos
     * @return type
     */
    function insertMany($tableName, &$pocos)
    {
        //if there were no records to insert, then no records are inserted.
        if (count($pocos) === 0) {
            return $pocos;
        }
        
        $convertedPocoKeys = [];
        //convert each poco to an object, if it isn't one already
        foreach ($pocos as $key => $poco) {
            if (!is_object($poco)) {
                $pocos[$key] = (object) $poco;
                $convertedPocoKeys[] = $key;
            }
        }

        $columnNames = array_keys(get_object_vars($pocos[0]));
        $hasId = in_array('id', $columnNames);

        $sql = "insert into $tableName(" . implode(',', $columnNames) . ')' .
                ' values (:' . implode(',:', $columnNames) . ')';
        $stmt = $this->prepare($sql);

        foreach ($pocos as $poco) {
            //convert certain data types into others for storage
            $processedPoco = $this->preprocessPoco($poco);

            $stmt->execute((array) $processedPoco);
            //save the id if the poco has an id property
            if ($hasId) {
                $poco->id = $this->lastInsertId();
            }
        }
        
        //convert these pocos back to arrays, since they were sent in as arrays
        foreach ($convertedPocoKeys as $key) {
            (array) $pocos[$key];
        }
        return $pocos;
    }

    /**
     * Based on certain assumptions made about the database, convert data into those formats
     * Assumption 1: all booleans are stored as 1 and 0.
     * Assumption 2: all dates are stored as ISO1601 strings
     * @param object $poco
     * @return array[string]object
     */
    private function preprocessPoco(&$poco)
    {
        $result = [];
        foreach ($poco as $key => $value) {
            if (is_bool($value)) {
                $result[$key] = $value ? 1 : 0;
            } elseif (is_a($poco, 'DateTime')) {
                $result[$key] = Common::GetDateString($value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    function update($tableName, &$poco)
    {
        $arr = [$poco];
        return $this->updateMany($tableName, $arr)[0];
    }

    function updateMany($tableName, &$pocos)
    {
        //if there were no records to insert, then no records are inserted.
        if (count($pocos) === 0) {
            return $pocos;
        }

        $columnNames = array_keys(get_object_vars($pocos[0]));

        $sql = "update $tableName set ";
        $parts = [];
        foreach ($columnNames as $columnName) {
            $parts[] = "$columnName=:$columnName";
        }
        $sql .= implode(',', $parts) . ' where id = :id';
        $stmt = $this->prepare($sql);

        foreach ($pocos as $poco) {
            $stmt->execute((array) $poco);
        }
        return $pocos;
    }
}
