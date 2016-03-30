<?php

class Pager {

    public $records;

    function __construct($columns, $sql, $pageSize) {
        $this->sql = $sql;
        $this->columns = $columns;
        $this->currentIteration = 0;
        $this->pageSize = $pageSize;
        $this->totalRecordCount = intval(DbManager::SingleColumnQuery("select count(*) $sql")[0]);
        $this->totalIterations = intval(ceil($this->totalRecordCount / $this->pageSize));
    }

    function next() {
        if ($this->currentIteration >= $this->totalIterations) {
            return false;
        }
        $offset = $this->currentIteration * $this->pageSize;
        if ($this->currentIteration === $this->totalIterations - 1) {
            $count = $this->totalRecordCount % $this->pageSize;
        } else {
            $count = $this->pageSize;
        }
        $this->currentIteration++;
        $this->records = DbManager::GetAllClassQuery("select $this->columns $this->sql limit $offset,$count", $count);
        return true;
    }

}
