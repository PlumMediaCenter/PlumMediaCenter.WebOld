<?php

require_once(dirname(__FILE__) . '/../../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../../../code/database/Table.class.php');

class TestTable extends UnitTestCase {

    function setUp() {
        
    }

    function testCreateNewTable() {
        //verify that the table does not exist
        $this->assertFalse(DbManager::TableExists("test_movie"));
        $table = new Table("test_movie");
        $table->addColumn("video_id", "int", "not null auto_increment");
        $table->addColumn("title", "char(100)");
        $table->applyTable();
        //verify that the table was created
        $this->assertTrue(DbManager::TableExists("test_movie"));

        //delete the table
        DbManager::nonQuery("drop table test_movie");
        //verify that the table has been deleted
        $this->assertFalse(DbManager::TableExists("test_movie"));
    }

    function testRemovedColumn() {
        //verify that the table does not exist
        $this->assertFalse(DbManager::TableExists("test_movie"));
        $table = new Table("test_movie");
        $table->addColumn("title", "char(100)");

        $table->addColumn("video_id", "int", "not null auto_increment");
        $table->applyTable();
        //insert a record into the table
        DbManager::nonQuery("insert into test_movie (title) values('myTitle')");
        //verify that the table has two columns
        $this->assertEqual(count(DbManager::query("show columns from test_movie")), 2);
        //remove one of the columns from the table
        $table->removeColumn("title");
        $table->applyTable();

        //verify that the table still exists and that it only has one column
        $this->assertEqual(count(DbManager::query("show columns from test_movie")), 1);

        //delete the table
        DbManager::nonQuery("drop table test_movie");
        //verify that the table has been deleted
        $this->assertFalse(DbManager::TableExists("test_movie"));
    }

    function testAddedColumn() {
        //verify that the table does not exist
        $this->assertFalse(DbManager::TableExists("test_movie"));
        $table = new Table("test_movie");
        $table->addColumn("title", "char(100)");
        $table->addColumn("video_id", "int", "not null auto_increment");
        $table->applyTable();
        //insert a record into the table
        //verify that the table has two columns
        $this->assertEqual(count(DbManager::query("show columns from test_movie")), 2);
        //remove one of the columns from the table
        $table->addColumn("plot", 'char(100)');
        DbManager::nonQuery("insert into test_movie (title, plot) values('myTitle', 'super cool plot')");
        $table->applyTable();

        //verify that the table still exists and that it has 3 columns
        $this->assertEqual(count(DbManager::query("show columns from test_movie")), 3);

        //delete the table
        DbManager::nonQuery("drop table test_movie");
        //verify that the table has been deleted
        $this->assertFalse(DbManager::TableExists("test_movie"));
    }

}

?>
