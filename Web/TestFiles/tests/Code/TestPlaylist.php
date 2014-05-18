<?php

require_once(dirname(__FILE__) . '/../../../code/Playlist.class.php');

class TestPlaylist extends UnitTestCase {

    private $username = "TestUser";
    private $playlistName = "My Test Playlist";

    function setUp() {
        Queries::clearPlaylist($this->username, $this->playlistName);
    }

    function tearDown() {
        Queries::clearPlaylist($this->username, $this->playlistName);
    }

    function testConstruct() {
        $p = new Playlist($this->username, $this->playlistName);
        $this->assertEqual($this->username, $p->getUsername());
        $this->assertEqual($this->playlistName, $p->getPlaylistName());
    }

    function testAdd() {
        $p = new Playlist($this->username, $this->playlistName);
        $p->add(12345);
        $list = $p->getPlaylistItems();
        $this->assertEqual(1, count($list));
        $this->assertEqual(12345, $list[0]->videoId);

        //add another item
        $p->add(5);
        $list = $p->getPlaylistItems();
        $this->assertEqual(2, count($list));
        $this->assertEqual(12345, $list[0]->videoId);
        $this->assertEqual(5, $list[1]->videoId);
    }

    function testAddAtIndex() {
        $p = new Playlist($this->username, $this->playlistName);
        $p->add(1);
        $p->add(3);

        //add an item in between the first and second item, at index 1
        $p->add(2, 1);

        $list = $p->getPlaylistItems();
        //verify there are 3 items in the list
        $this->assertEqual(3, count($list));
        //verify that they are in the correct order
        $this->assertEqual(1, $list[0]->videoId);
        $this->assertEqual(2, $list[1]->videoId);
        $this->assertEqual(3, $list[2]->videoId);
    }

    function testWriteToDatabase() {
        $p = new Playlist($this->username, $this->playlistName);
        $p->add(5);
        $p->add(7);
        //make sure that the object successfully writes to the database
        $this->assertTrue($p->writeToDb());
        $results = DbManager::query("select username, name, idx, video_id from playlist where username='$this->username' and name = '$this->playlistName'  order by idx asc");
        $this->assertEqual($results[0]->username, $this->username);
        $this->assertEqual($results[0]->name, $this->playlistName);
        $this->assertEqual($results[0]->idx, 0);
        $this->assertEqual($results[0]->video_id, 5);

        $this->assertEqual($results[1]->username, $this->username);
        $this->assertEqual($results[1]->name, $this->playlistName);
        $this->assertEqual($results[1]->idx, 1);
        $this->assertEqual($results[1]->video_id, 7);

        //add an element in the middle and make sure the database gets the new value and updates the order
        $p->add(6, 1);
        $this->assertTrue($p->writeToDb());
        $results = DbManager::query("select username, name, idx, video_id from playlist where username='$this->username' and name = '$this->playlistName' order by idx asc");
        $this->assertEqual($results[0]->username, $this->username);
        $this->assertEqual($results[0]->name, $this->playlistName);
        $this->assertEqual($results[0]->idx, 0);
        $this->assertEqual($results[0]->video_id, 5);

        $this->assertEqual($results[1]->username, $this->username);
        $this->assertEqual($results[1]->name, $this->playlistName);
        $this->assertEqual($results[1]->idx, 1);
        $this->assertEqual($results[1]->video_id, 6);

        $this->assertEqual($results[2]->username, $this->username);
        $this->assertEqual($results[2]->name, $this->playlistName);
        $this->assertEqual($results[2]->idx, 2);
        $this->assertEqual($results[2]->video_id, 7);
    }

    function testClear() {
        $p = new Playlist($this->username, $this->playlistName);
        $p->addRange(array(5, 6, 7));
        $vals = $p->getPlaylistItems();
        $this->assertEqual(3, count($vals));

        $p->clear();
        $vals = $p->getPlaylistItems();
        $this->assertEqual(0, count($vals));
    }

    function testRemove() {
        $p = new Playlist($this->username, $this->playlistName);
        $p->addRange(array(5, 6, 7));
        $vals = $p->getPlaylistItems();
        $this->assertEqual(3, count($vals));

        //remove the item at the end
        $this->assertTrue($p->remove(2));
        $vals = $p->getPlaylistItems();
        $this->assertEqual(2, count($vals));
        //make sure the correct item was removed
        $this->assertEqual(5, $vals[0]);
        $this->assertEqual(6, $vals[1]);

        //reset
        $p->clear();
        $p->addRange(array(5, 6, 7));

        //remove the item at the middle
        $this->assertTrue($p->remove(1));
        $vals = $p->getPlaylistItems();
        $this->assertEqual(2, count($vals));
        //make sure the correct item was removed
        $this->assertEqual(5, $vals[0]);
        $this->assertEqual(7, $vals[1]);

        //reset
        $p->clear();
        $p->addRange(array(5, 6, 7));

        //remove the item at the beginning
        $this->assertTrue($p->remove(0));
        $vals = $p->getPlaylistItems();
        $this->assertEqual(2, count($vals));
        //make sure the correct item was removed
        $this->assertEqual(6, $vals[0]);
        $this->assertEqual(7, $vals[1]);

        //reset
        $p->clear();
        $p->addRange(array(5, 6, 7));

        //try removing an item with a negative index
        $this->assertFalse($p->remove(-1));
        //try removing an item above the max number of items
        $this->assertFalse($p->remove(3));
    }

    function testRemoveFromDb() {
        $p = new Playlist($this->username, $this->playlistName);
        $p->addRange(array(5, 6, 7));
        //save this to the database
        $p->writeToDb();

        //reset
        $p->clear();
        $p->addRange(array(5, 6, 7));

        //remove the item at the end
        $this->assertTrue($p->remove(2));
        $p->writeToDb();
        //get the data from the database. make sure it has what we put there
        $results = DbManager::query("select username, name, idx, video_id from playlist where username='$this->username' and name = '$this->playlistName'  order by idx asc");
        $this->assertEqual(2, count($results));
        $this->assertEqual($results[0]->username, $this->username);
        $this->assertEqual($results[0]->name, $this->playlistName);
        $this->assertEqual($results[0]->idx, 0);
        $this->assertEqual($results[0]->video_id, 5);

        $this->assertEqual($results[1]->username, $this->username);
        $this->assertEqual($results[1]->name, $this->playlistName);
        $this->assertEqual($results[1]->idx, 1);
        $this->assertEqual($results[1]->video_id, 6);

        //reset
        $p->clear();
        $p->addRange(array(5, 6, 7));

        //remove the item at the middle
        $this->assertTrue($p->remove(1));
        $p->writeToDb();
        //get the data from the database. make sure it has what we put there
        $results = DbManager::query("select username, name, idx, video_id from playlist where username='$this->username' and name = '$this->playlistName'  order by idx asc");
        $this->assertEqual(2, count($results));
        $this->assertEqual($results[0]->username, $this->username);
        $this->assertEqual($results[0]->name, $this->playlistName);
        $this->assertEqual($results[0]->idx, 0);
        $this->assertEqual($results[0]->video_id, 5);

        $this->assertEqual($results[1]->username, $this->username);
        $this->assertEqual($results[1]->name, $this->playlistName);
        $this->assertEqual($results[1]->idx, 1);
        $this->assertEqual($results[1]->video_id, 7);

        //reset
        $p->clear();
        $p->addRange(array(5, 6, 7));

        //remove the item at the beginning
        $this->assertTrue($p->remove(0));
        $p->writeToDb();
        //get the data from the database. make sure it has what we put there
        $results = DbManager::query("select username, name, idx, video_id from playlist where username='$this->username' and name = '$this->playlistName'  order by idx asc");
        $this->assertEqual(2, count($results));
        $this->assertEqual($results[0]->username, $this->username);
        $this->assertEqual($results[0]->name, $this->playlistName);
        $this->assertEqual($results[0]->idx, 0);
        $this->assertEqual($results[0]->video_id, 6);

        $this->assertEqual($results[1]->username, $this->username);
        $this->assertEqual($results[1]->name, $this->playlistName);
        $this->assertEqual($results[1]->idx, 1);
        $this->assertEqual($results[1]->video_id, 7);


        //reset
        $p->clear();
        $p->addRange(array(5, 6, 7));
    }

    function testLoadFromDb() {
        $p = new Playlist($this->username, $this->playlistName);
        $p->addRange(array(7, 8, 9));
        $p->writeToDb();
        //make sure that it loads correctly using the object
        $p = new Playlist($this->username, $this->playlistName);
        $p->loadFromDb();
        $list = $p->getPlaylistItems();
        $this->assertEqual($list[0], 7);
        $this->assertEqual($list[1], 8);
        $this->assertEqual($list[2], 9);

        //remove an item
        $p->remove(0);
        $p->writeToDb();

        //make sure that it loads correctly using the object
        $p = new Playlist($this->username, $this->playlistName);
        $p->loadFromDb();
        $list = $p->getPlaylistItems();
        $this->assertEqual($list[0], 8);
        $this->assertEqual($list[1], 9);

        $p = new Playlist($this->username, $this->playlistName);
        $p->addRange(array(7, 8, 9));
        $p->writeToDb();

        $p->remove(1);
        $p->writeToDb();

        $p->loadFromDb();
        $list = $p->getPlaylist();
        $this->assertEqual($list[0], 7);
        $this->assertEqual($list[1], 9);

        $p = new Playlist($this->username, $this->playlistName);
        $p->addRange(array(7, 8, 9));
        $p->writeToDb();

        $p->remove(2);
        $p->writeToDb();

        $p->loadFromDb();
        $list = $p->getPlaylistItems();
        $this->assertEqual($list[0], 7);
        $this->assertEqual($list[1], 8);
    }
}

?>
