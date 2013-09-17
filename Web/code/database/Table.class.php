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
    function addColumn($columnName, $dataType, $extraStuff = null) {
        $this->columns[] = new DbColumn($columnName, $dataType, $extraStuff);
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
        foreach ($this->columns as $col /* @var $col DbColumn */) {
            $sql .= " $comma $col->columnName $col->dataType $col->extraStuff";
            $comma = ",";
        }
        $sql .=")";
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

        //add any new columns
        foreach ($this->columns as $column) {
            //see if this column is in the database already. if not, it is a new column
        }

        //apply the alter statements
        $alter = "alter table $this->tableName";
        $bTotalSuccess = true;
        foreach ($statements as $s) {
            $bTotalSuccess = $bTotalSuccess && DbManager::nonQuery("$alter $s");
        }
        return $bTotalSuccess;
    }
    
    private function columnExistsInDatabase($columnName, $columnsFromDatabase){
        foreach($columnsFromDatabase as $colFromDb){
            if($columnName === $colFromDb->Field){
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

    function __construct($columnName, $dataType, $extraStuff) {
        $this->columnName = $columnName;
        $this->dataType = $dataType;
        $this->extraStuff;
    }

}
?>
