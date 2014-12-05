<?php

require_once(dirname(__FILE__) . '/../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../../code/DbManager.class.php');

class TestDbManager extends UnitTestCase {

    function setUp() {
        
    }

    function testTableExists() {
        //the table doesn't exist yet, make sure we correctly determine that
        $this->assertFalse(DbManager::TableExists("test_movie"));
        //create the table
        DbManager::query("create table test_movie(video_id int)");
        //the table exists now. make sure we know that
        $this->assertTrue(DbManager::TableExists("test_movie"));
        //delete the table
        DbManager::query("drop table test_movie");
    }

    function testGenerateNotInStatement() {
        $arr = [1];
        $s = DbManager::GenerateNotInStatement($arr, false, 1);
        $this->assertEqual(" not in(1) ", $s);

        $arr = [1, 2];
        $s = DbManager::GenerateNotInStatement($arr, false, 1);
        $this->assertEqual(" not in(1) and not in(2) ", $s);

        $arr = [1, 2, 3, 4, 5];
        $s = DbManager::GenerateNotInStatement($arr, false, 2);
        $this->assertEqual(" not in(1,2) and not in(3,4) and not in(5) ", $s);

        $arr = [1, 2, 3, 4, 5];
        $s = DbManager::GenerateNotInStatement($arr, false, 3);
        $this->assertEqual(" not in(1,2,3) and not in(4,5) ", $s);

        $arr = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $s = DbManager::GenerateNotInStatement($arr, false, 10);
        $this->assertEqual(" not in(1,2,3,4,5,6,7,8,9,10) ", $s);

        $arr = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
        $s = DbManager::GenerateNotInStatement($arr, false, 10);
        $this->assertEqual(" not in(1,2,3,4,5,6,7,8,9,10) and not in(11) ", $s);
    }

}

?>
