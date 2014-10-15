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
    //this is a list of all db upgrade functions that are callable, in order. 
    private static $upgradeFunctionNames = array("001_000" => "db001_000");

    function __construct($rootUsername, $rootPassword, $dbHost) {
        $this->rootUsername = $rootUsername;
        $this->rootPassword = $rootPassword;
        $this->dbHost = $dbHost;
        $upgradeFunctionNames = [];
    }

    /**
     * Determines whether or not the database is up to date with the latest php application
     * @return boolean - true if the database is structured in the latest version, false if not
     */
    static function DatabaseIsUpToDate() {
        $cur = CreateDatabase::CurrentDbVersion();
        $latest = CreateDatabase::LatestDbVersion();
        return $cur == $latest;
    }

    /**
     * Returns the latest app version
     * @return string - the latest version possible of this application
     */
    static function LatestDbVersion() {
        $names = array_unique(CreateDatabase::$upgradeFunctionNames);
        end($names);
        $key = key($names);

        $latestVersion = str_replace("_", ".", $key);
        return $latestVersion;
    }

    /**
     * Returns the current app version in the database. 
     * @return string - the current app version in the database
     */
    static function CurrentDbVersion($dbHost = null, $dbUsername = null, $dbPassword = null) {
        $version;
        $tableExists = DbManager::TableExists("app_version");
        //see if the version table exists. If it doesn't, then we start at the beginning.
        if ($tableExists === false) {
            $version = "000.000";
        } else {
            $version = DbManager::GetSingleItem("select * from app_version", $dbHost, $dbUsername, $dbPassword, config::$dbName);
            //replace the periods with underscores
        }
        return $version;
    }

    /**
     * Upgrades the database. Determines what version the current database is at, and then
     * implements all version upgrades until it is up to date
     * @return boolean - true if total success, false if at least one item fails
     */
    function upgradeDatabase() {
        $dbVersion = CreateDatabase::CurrentDbVersion($this->dbHost, $this->rootUsername, $this->rootPassword);
        $dbVersion = str_replace(".", "_", $dbVersion);

        //execute all update functions in order, starting with the version AFTER the version we currently have
        foreach (CreateDatabase::$upgradeFunctionNames as $key => $funct) {
            if ($key > $dbVersion) {
                //call the upgrade function
                $this->$funct();
            }
        }
        return true;
    }

    function db001_000() {
        writeToLog("Creating database user and schema");
        //log on as root and create the database
        $this->createVideoDatabase($this->rootUsername, $this->rootPassword, $this->dbHost);

        writeToLog("Creating app_version table");
        DbManager::NonQuery("
            create table app_version(
                version varchar(10)
                )");
        writeToLog("Updating app_version number to 1.0");
        DbManager::NonQuery("insert into app_version(version) values('001.000')");

        writeToLog("Creating video table");
        DbManager::NonQuery(" 
            create table video(
                video_id int not null auto_increment primary key,
                title varchar(100),
                running_time_seconds int(5),
                plot varchar(3000),
                path varchar(1000) not null,
                url varchar(2000),
                filetype varchar(15) not null,
                metadata_last_modified_date datetime,
                poster_last_modified_date datetime,
                mpaa varchar(200),
                release_date date,
                media_type varchar(10) not null,
                video_source_path varchar(2000) not null,
                video_source_url varchar(2000) not null
            )");

        writeToLog("Creating tv_episode table");
        DbManager::NonQuery(" 
            create table tv_episode(
               video_id int not null primary key,
               tv_show_video_id int not null,
               season_number int not null,
               episode_number int not null,
               writer varchar(50),
               director varchar(50),
               foreign key (video_id) references video(video_id),
               foreign key (tv_show_video_id) references video(video_id)
        )");
        writeToLog("Creating video_source table");
        DbManager::NonQuery(" 
            create table video_source(
               location varchar(200) not null primary key,
               base_url varchar(2000) not null,
               media_type varchar(10) not null,
               security_type varchar(20) not null,
               refresh_videos boolean default 0
        )");
        writeToLog("Creating watch_video table");
        DbManager::NonQuery(" 
            create table watch_video(
                username varchar(128) not null,
                video_id int not null,
                time_in_seconds int(10) not null,
                date_watched datetime not null,
                primary key (username, video_id),
                foreign key (video_id) references video (video_id)
        )");
        writeToLog("Creating tv_episode_v view");
        DbManager::nonQuery("CREATE OR REPLACE VIEW tv_episode_v
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

        writeToLog("Database creating complete");
    }

    private function createVideoDatabase($rootUsername, $rootPassword, $host) {
        $user = config::$dbUsername;
        $pass = config::$dbPassword;
        $db = config::$dbName;

        try {
            $dbh = DbManager::GetPdo($host, $rootUsername, $rootPassword);
            //delete any previous references to the user or the database
            //$dbh->exec("delete from mysql.user where user = 'plumvideoplayer';");
            //$dbh->exec("drop user 'plumvideoplayer'@'localhost';");
            // $dbh->exec("drop database plumvideoplayer;");
            //create the database, if it doesn't already exist
            $success = $dbh->exec("CREATE DATABASE `$db`;");
            $success = $dbh->exec("CREATE USER '$user'@'$host' IDENTIFIED BY '$pass';");
            $success = $dbh->exec("GRANT ALL ON `$db`.* TO '$user'@'$host';");
            $success = $dbh->exec("GRANT ALL ON `$db`.* TO '$user'@'192.168.1.%';");
            $success = $dbh->exec("FLUSH PRIVILEGES;");
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
