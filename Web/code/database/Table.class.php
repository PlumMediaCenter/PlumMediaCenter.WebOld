<?php

include_once(dirname(__FILE__) . "/../DbManager.class.php");

class Table {

    private $tableName;
    private $columns;

    function __construct($tableName) {
        $this->tableName = $tableName;
        $this->columns = [];
    }

    /**
     * Add a column to this object, which when applied will then apply to the underlying table in the db
     * @param type $columnName - the columnName of the column
     * @param type $dataType - the dataType of the column (i.e. int, char(10), etc...)
     * @param type $extraStuff - any extra items that are used for a column (not null, unique primary key, etc...)
     */
    function addColumn($columnName, $dataType, $extraStuff = "", $primaryKey = false) {
        $this->columns[] = new DbColumn($columnName, $dataType, $extraStuff, $primaryKey);
    }

    /**
     * Remove a column from the list of columns that will be created for this table
     * **NOTE: this does not add a column to a list of columns to be deleted from the database. This
     * deletes the colum from this object only!
     * @param string $columnName - the name of the column to be removed from the list of columns in this list already.
     */
    function removeColumn($columnName) {
        foreach ($this->columns as $key => $column) {
            if ($column->columnName === $columnName) {
                //delete the column
                unset($this->columns[$key]);
            }
        }
    }

    /**
     * Applies the table to the database. If the table already exists, a comparison is done to see what has changed and only performs those changes.
     * If the table does not exist, the table is created.
     */
    public function applyTable() {
        //determine if this is a new table
        if (DbManager::TableExists($this->tableName) === false) {
            //this is a new table. 
            return $this->createNewTable();
        } else {
            return $this->updateExistingTable();
        }
    }

    /**
     * Creates a new table based on the columns of the table
     * @return boolean - true if successfully created table, false if unsuccessful
     */
    private function createNewTable() {
        //generate the create table statement
        $sql = "create table $this->tableName(";
        $comma = "";
        $keyNameList = [];
        foreach ($this->columns as $column /* @var $column DbColumn */) {
            $sql .= " $comma $column->columnName $column->dataType $column->extraStuff";
            $comma = ",";
            if ($column->primaryKey === true) {
                $keyNameList[] = $column->columnName;
            }
        }
        //generate the primary key list, if any are present
        $primaryKeySql = "";
        if (count($keyNameList) > 0) {
            $primaryKeySql = "primary key(";
            $comma = "";
            foreach ($keyNameList as $keyName) {
                $primaryKeySql = "$primaryKeySql $comma $keyName";
                $comma = ",";
            }
            $primaryKeySql = ",$primaryKeySql)";
        }
        $sql = "$sql $primaryKeySql)";
        return DbManager::nonQuery($sql);
    }

    /**
     * Updates the existing table, adding any new columns and deleting any existing columns. 
     * @return boolean - true if successfully performed all operations necessary to update the table, false if at least one statement failed
     */
    private function updateExistingTable() {
        $statements = []; // this will be a list of alter table statements, but only the stuff AFTER the 'alter table [tablename]' part
        //get the list of current columns in this table
        $existingColumnsFromDb = DbManager::query("show columns from $this->tableName");

        foreach ($existingColumnsFromDb as $colFromDb) {
            //if the column was not found in the list, this column that is 
            ////currently in the database will need to be deleted
            if ($this->getColumn($colFromDb->Field) === null) {
                $statements[] = "drop column $colFromDb->Field";
            }
        }
        //a list of names of columns that make up the primary key
        $keyNameList = [];
        //add any new columns or update existing ones
        foreach ($this->columns as $column) {
            //see if this column does not exist in the table, it is a new column. generate a new column stmt
            if ($this->columnExistsInTable($column->columnName, $existingColumnsFromDb) === false) {
                $statements[] = "add column $column->columnName $column->dataType $column->extraStuff";
            } else {
                //these columns exist in the table already. create update statements for each column, even if nothing has changed. it won't harm the column
                $statements[] = "modify column $column->columnName $column->dataType $column->extraStuff";
            }
            //add this column to the list of primary keys if it is flagged as such
            if ($column->primaryKey === true) {
                $keyNameList[] = $column->columnName;
            }
        }

        //reregister the primary keys
        $primaryKeySql = "";
        if (count($keyNameList) > 0) {
            $primaryKeySql = "drop primary key, add primary key(";
            $comma = "";
            foreach ($keyNameList as $keyName) {
                $primaryKeySql = "$primaryKeySql $comma $keyName";
                $comma = ",";
            }
            $primaryKeySql = "$primaryKeySql)";
            $statements[] = $primaryKeySql;
        }

        //
        //apply the alter statements
        $alter = "alter table $this->tableName";
        $bTotalSuccess = true;
        foreach ($statements as $s) {
            $bTotalSuccess = $bTotalSuccess && DbManager::nonQuery("$alter $s");
        }
        return $bTotalSuccess;
    }

    /**
     * Determine if a particular column exists in a table. This only checks by name, and not by data type
     * @param string $columnName - the name of the column
     * @param object[] $columnsFromDatabase - an array of column definitions from a 'show columns' query 
     * @return boolean - true if the column exists in the table, false if it does not
     */
    private function columnExistsInTable($columnName, $columnsFromDatabase) {
        foreach ($columnsFromDatabase as $colFromDb) {
            if ($columnName === $colFromDb->Field) {
                return true;
            }
        }
        //if we got to here, the column is NOT in the db
        return false;
    }

    /**
     * Returns the column
     * @param type $columnName
     * @return null
     */
    private function getColumn($columnName) {
        foreach ($this->columns as $key => $col) {
            if ($col->columnName === $columnName) {
                return $col;
            }
        }
        //if the column was not found, return null
        return null;
    }

}

Class DbColumn {

    public $columnName;
    public $dataType;
    public $extraStuff;
    public $primaryKey;

    function __construct($columnName, $dataType, $extraStuff = "", $primaryKey = false) {
        $extraStuff = $extraStuff == null ? "" : $extraStuff;
        $primaryKey = $primaryKey == null ? false : $primaryKey;
        $this->columnName = $columnName;
        $this->dataType = $dataType;
        $this->extraStuff = $extraStuff;
        $this->primaryKey = $primaryKey;
    }

}
?>
