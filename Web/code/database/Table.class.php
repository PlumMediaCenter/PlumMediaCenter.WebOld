<?php
/**
 * A class that allows a user to define a table structure. If the table already exists in the database, it dynamically
 * determines what needs to change and changes it, while trying to preserve the existing data
 */
class Table {
    private $tableName;
    function __construct($tableName){
        $this->tableName = $tableName;
    }
}

?>
