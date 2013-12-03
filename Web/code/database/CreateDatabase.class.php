<?php

include_once(dirname(__FILE__) . "/../DbManager.class.php");
include_once(dirname(__FILE__) . "/Table.class.php");
include_once(dirname(__FILE__) . "/../functions.php");

/**
 * Creates the entire database
 */
class CreateDatabase {

    private $rootUsername;
    private $rootPassword;
    private $dbHost;

    function __construct($rootUsername, $rootPassword, $dbHost) {
        $this->rootUsername = $rootUsername;
        $this->rootPassword = $rootPassword;
        $this->dbHost = $dbHost;
    }

    function createDatabase() {
        writeToLog("Creating database");
        $totalSuccess = true;
        //log in to the db as root and create the video database
        $totalSuccess = $totalSuccess && $this->createVideoDatabase($this->rootUsername, $this->rootPassword, $this->dbHost);
        //create all tables
        $totalSuccess = $totalSuccess && $this->table_video();
        writeToLog("Created video table: $totalSuccess");
        $totalSuccess = $totalSuccess && $this->table_tv_episode();
        writeToLog("Created tv_episode table: $totalSuccess");
        $totalSuccess = $totalSuccess && $this->table_video_source();
        writeToLog("Created video_source table: $totalSuccess");
        $totalSuccess = $totalSuccess && $this->table_watch_video();
        writeToLog("Created watch_video table: $totalSuccess");
        $totalSuccess = $totalSuccess && $this->view_tv_episode_v();
        writeToLog("Created tv_episode view: $totalSuccess");
        $totalSuccess = $totalSuccess && $this->table_playlist_name();
        writeToLog("Created playlist_name table: $totalSuccess");
        $totalSuccess = $totalSuccess && $this->table_playlist();
        writeToLog("Created playlist table: $totalSuccess");

        writeToLog("Finished creating database");
        return $totalSuccess;
    }

    private function createVideoDatabase($rootUsername, $rootPassword, $host) {
        $user = config::$dbUsername;
        $pass = config::$dbPassword;
        $db = config::$dbName;

        try {
            $dbh = new PDO("mysql:host=$host", $rootUsername, $rootPassword);
            //delete any previous references to the user or the database
            //$dbh->exec("delete from mysql.user where user = 'plumvideoplayer';");
            //$dbh->exec("drop user 'plumvideoplayer'@'localhost';");
            // $dbh->exec("drop database plumvideoplayer;");
            //create the database, if it doesn't already exist
            $dbh->exec("CREATE DATABASE `$db`;");
            $dbh->exec("CREATE USER '$user'@'$host' IDENTIFIED BY '$pass';");
            $dbh->exec("GRANT ALL ON `$db`.* TO '$user'@'$host';");
            $dbh->exec("GRANT ALL ON `$db`.* TO '$user'@'192.168.1.%';");
            $dbh->exec("FLUSH PRIVILEGES;");
        } catch (PDOException $e) {
            writeToLog($e);
            //die("DB ERROR: " . $e->getMessage());
            return false;
        }
        return true;
    }

    private function view_tv_episode_v() {
        return DbManager::nonQuery("CREATE OR REPLACE VIEW tv_episode_v
            AS
                SELECT v.video_id,
                    v.title,
                    t.tv_show_video_id,
                    t.season_number,
                    t.episode_number,
                    v.running_time_seconds,
                    v.path,
                    v.url,
                    v.filetype,
                    v.metadata_last_modified_date,
                    v.poster_last_modified_date,
                    v.mpaa,
                    v.release_date,
                    v.media_type,
                    v.video_source_path,
                    v.video_source_url,
                    t.writer,
                    t.director,
                    v.plot
                FROM video v, tv_episode t
                WHERE v.video_id = t.video_id");
    }

    private function table_video() {
        $t = new Table(config::$dbName, "video");
        $t->addColumn("video_id", "int", "not null auto_increment", true);
        $t->addColumn("title", "char(100)", "");
        $t->addColumn("running_time_seconds", "int(5)", "");
        $t->addColumn("plot", "varchar(3000)", "");
        $t->addColumn("path", "varchar(1000)", "not null");
        $t->addColumn("url", "varchar(2000)", "");
        $t->addColumn("filetype", "char(15)", "");
        $t->addColumn("metadata_last_modified_date", "datetime", "");
        $t->addColumn("poster_last_modified_date", "datetime", "");
        $t->addColumn("mpaa", "char(200)", "");
        $t->addColumn("release_date", "date", "");
        $t->addColumn("media_type", "char(10)", "not null");
        $t->addColumn("video_source_path", "varchar(2000)", "not null");
        $t->addColumn("video_source_url", "varchar(2000)", "not null");
        return $t->applyTable();
    }

    private function table_tv_episode() {
        $t = new Table(config::$dbName, "tv_episode");
        $t->addColumn("video_id", "int", "not null", true);
        $t->addColumn("tv_show_video_id", "int", "not null");
        $t->addColumn("season_number", "int", "");
        $t->addColumn("episode_number", "int", "");
        $t->addColumn("writer", "char(50)", "");
        $t->addColumn("director", "char(50)", "");
        return $t->applyTable();
    }

    private function table_video_source() {
        $t = new Table(config::$dbName, "video_source");
        $t->addColumn("location", "char(200)", "", true);
        $t->addColumn("base_url", "char(200)", "");
        $t->addColumn("media_type", "char(10)", "");
        $t->addColumn("security_type", "char(20)", "");
        $t->addColumn("refresh_videos", "boolean", "default 0");
        return $t->applyTable();
    }

    private function table_watch_video() {
        $t = new Table(config::$dbName, "watch_video");
        $t->addColumn("username", "char(128)", "", true);
        $t->addColumn("video_id", "int", "not null", true);
        $t->addColumn("time_in_seconds", "int(10)", "");
        $t->addColumn("date_watched", "datetime", "");
        return $t->applyTable();
    }

    private function table_playlist() {
        $t = new Table(config::$dbName, "playlist");
        $t->addColumn("item_id", "int", "not null", true);
        $t->addColumn("username", "char(128)");
        $t->addColumn("name", "char(128)");
        $t->addColumn("idx", "int");
        $t->addColumn("video_id", "int");
        return $t->applyTable();
    }

    private function table_playlist_name() {
        $t = new Table(config::$dbName, "playlist_name");
        $t->addColumn("username", "char(128)", "", true);
        $t->addColumn("name", "char(128)", "", true);
        return $t->applyTable();
    }

}

?>
