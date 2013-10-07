<?php

require_once(dirname(__FILE__) . '/../../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../../../code/database/Table.class.php');

class TestTable extends UnitTestCase {

    private $table;

    /**
     * Create the test_movie table before every test, add the video_id and title columns to it
     */
    function setUp() {
        //verify that the table does not exist
        $this->assertFalse(DbManager::TableExists("test_movie"));
        $this->table = new Table("test_movie");
        $this->table->addColumn("video_id", "int", "not null auto_increment", true);
        $this->table->addColumn("title", "char(100)");
        $this->table->applyTable();
        //verify that the table was created
        $this->assertTrue(DbManager::TableExists("test_movie"));
    }

    /**
     * delete the test_movie table before every test
     */
    function tearDown() {
        //delete the table
        DbManager::nonQuery("drop table test_movie");
        //verify that the table has been deleted
        $this->assertFalse(DbManager::TableExists("test_movie"));
    }

    function testCreateNewTable() {
        //nothing should have to be done here, since setup and teardown will actually perform all of the tests necessary
    }

    function testRemovedColumn() {

        //insert a record into the table
        DbManager::nonQuery("insert into test_movie (title) values('myTitle')");
        //verify that the table has the right number of columns
        $this->assertEqual(count(DbManager::query("show columns from test_movie")), 2);
        //remove one of the columns from the table
        $this->table->removeColumn("title");
        $this->table->applyTable();

        //verify that the table still exists and that it has the right number of columns
        $this->assertEqual(count(DbManager::query("show columns from test_movie")), 1);
    }

    function testAddedColumn() {

        //verify that the table has the right number of columns
        $this->assertEqual(count(DbManager::query("show columns from test_movie")), 2);
        //insert a record into the table
        DbManager::nonQuery("insert into test_movie (title) values('myTitle')");
        //verify that the data is in the table
        $row = DbManager::queryGetSingleRow("select * from test_movie");
        //verify that we found a single row
        $this->assertTrue($row !== false);
        $this->assertEqual($row->title, "myTitle");
        //this is the first row inserted, make sure the auto_increment worked
        $this->assertEqual($row->video_id, 1);

        //add a new column to the table
        $this->table->addColumn("plot", 'char(100)');
        $this->table->applyTable();

        //verify that the table still exists and that it has the right number of columns
        $this->assertEqual(count(DbManager::query("show columns from test_movie")), 3);

        //verify that the data is still the table
        $row = DbManager::queryGetSingleRow("select * from test_movie");
        $this->assertEqual($row->title, "myTitle");
    }

    function testModifiedColumn_AddPrimaryKey() {
        //create the table with two columns defined as the primary key
        $this->table->addColumn("file_path", "char(100)", "not null", true);
        $this->table->applyTable();

        //verify that the table has the right number of columns
        $this->assertEqual(count(DbManager::query("show columns from test_movie")), 3);
        //insert a record into the table
        DbManager::nonQuery("insert into test_movie (title, file_path) values('myTitle', '/var/www/website/')");
        //verify that the data is in the table
        $row = DbManager::queryGetSingleRow("select * from test_movie");
        //verify that we found a single row
        $this->assertTrue($row !== false);
        $this->assertEqual($row->title, "myTitle");
        //this is the first row inserted, make sure the auto_increment worked
        $this->assertEqual($row->video_id, 1);
        $this->assertEqual($row->file_path, '/var/www/website/');

        $cols = DbManager::query("show columns from test_movie");
        //verify that the video_id column is recognized as a primary key
        $this->assertEqual($cols[0]->Key, "PRI");
        //verify that the title columns NOT marked as primary key
        $this->assertIdentical($cols[1]->Key, "");
        //verify that the file_path column is recognized as a primary key
        $this->assertEqual($cols[2]->Key, "PRI");
    }

    function testModifiedColumn_RemovePrimaryKey() {
        $this->testModifiedColumn_AddPrimaryKey();
        //modify the table by removing the primary key constraint on the file_path column
        $this->table->removeColumn("file_path");
        $this->table->addColumn("file_path", "char(100)", "not null", false);
        $this->table->applyTable();

        //verify that the table still exists and that it has 3 columns
        $cols = DbManager::query("show columns from test_movie");
        $this->assertEqual(count($cols), 3);

        $row = DbManager::queryGetSingleRow("select * from test_movie");
        //verify that we found a single row
        $this->assertTrue($row !== false);
        $this->assertEqual($row->title, "myTitle");
        $this->assertEqual($row->video_id, 1);
        $this->assertEqual($row->file_path, '/var/www/website/');

        //verify that the video_id column is recognized as a primary key
        $this->assertEqual($cols[0]->Key, "PRI");
        //verify that the title column is NOT marked as primary key
        $this->assertIdentical($cols[1]->Key, "");
        //verify that the file_path column is  NOT marked as primary key
        $this->assertEqual($cols[2]->Key, "");
    }

    function testChangedCharacterLength() {
        //add data to the table
        DbManager::nonQuery("insert into test_movie (title) values('this is a title')");

        //change the character length of the title column
        $this->table->removeColumn("title");
        $this->table->addColumn("title", "char(50)");
        $this->table->applyTable();
        //verify that the column has been updated correctly
        $cols = DbManager::query("show columns from test_movie");
        $this->assertEqual($cols[1]->Type, "char(50)");

        //verify that the data in that column still exists
        $row = DbManager::queryGetSingleRow("select * from test_movie");
        //verify that we found a single row
        $this->assertTrue($row !== false);
        $this->assertEqual($row->title, "this is a title");
    }

}

?>
