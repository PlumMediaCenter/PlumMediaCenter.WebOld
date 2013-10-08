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

}

?>
