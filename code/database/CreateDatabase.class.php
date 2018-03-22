<?php

include_once(dirname(__FILE__) . "/../DbManager.class.php");
include_once(dirname(__FILE__) . "/../controllers/VideoController.php");
include_once(dirname(__FILE__) . "/../functions.php");

/**
 * Creates the entire database
 */
class CreateDatabase {

    private $rootUsername;
    private $rootPassword;
    private $dbHost;
    //this is a list of all db upgrade functions that are callable, in order. 
    private static $upgradeFunctionNames = array(
        '0.1.0' => 'db0_1_0',
        '0.1.1' => 'db0_1_1',
        '0.1.2' => 'db0_1_2',
        '0.1.3' => 'db0_1_3',
        '0.1.4' => 'db0_1_4',
        '0.1.5' => 'db0_1_5',
        '0.1.6' => 'db0_1_6',
        '0.1.7' => 'db0_1_7',
        '0.1.8' => 'db0_1_8',
        '0.2.0' => 'db0_2_0',
        '0.2.1' => 'db0_2_1',
        '0.2.2' => 'db0_2_2',
        '0.3.0' => 'db0_3_0',
        '0.3.1' => 'db0_3_1',
        '0.3.2' => 'db0_3_2',
        '0.3.3' => 'db0_3_3',
        '0.3.4' => 'db0_3_4',
        '0.3.5' => 'db0_3_5',
        '0.3.6' => 'db0_3_6',
        '0.3.7' => 'db0_3_7',
        '0.3.8' => 'db0_3_8',
        '0.3.9' => 'db0_3_9',
        '0.3.10' => 'db0_3_10',
        '0.3.11' => 'db0_3_11',
        '0.3.12' => 'db0_3_12',
        '0.3.13' => 'db0_3_13',
        '0.3.14' => 'db0_3_14',
        '0.3.15' => 'db0_3_15',
        '0.3.16' => 'db0_3_16',
        '0.3.17' => 'db0_3_17',
        '0.3.18' => 'db0_3_18',
        '0.3.19' => 'db0_3_19',
        '0.3.20' => 'db0_3_20',
        '0.3.21' => 'db0_3_21',
        '0.3.22' => 'db0_3_22',
        '0.3.23' => 'db0_3_23',
        '0.3.24' => 'db0_3_24',
        '0.3.25' => 'db0_3_25',
        '0.3.26' => 'db0_3_26',
        '0.3.27' => 'db0_3_27',
        '0.3.28' => 'db0_3_28',
        '0.3.30' => 'db0_3_30',
        '0.3.31' => 'db0_3_31'
    );

    function __construct($rootUsername, $rootPassword, $dbHost) {
        $this->rootUsername = $rootUsername;
        $this->rootPassword = $rootPassword;
        $this->dbHost = $dbHost;
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
            //die("DB ERROR: " . $e->getMessage());
            return false;
        }
        return true;
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
        $version = '0.0.0';
        $tableExists = DbManager::TableExists("app_version");
        //see if the version table exists. If it doesn't, then we start at the beginning.
        if ($tableExists === false) {
            
        } else {
            $version = DbManager::GetSingleItem("select * from app_version", $dbHost, $dbUsername, $dbPassword, config::$dbName);
            //handle the first version number, which was not following semantic versioning
            if ($version === "001.000") {
                $version = "0.1.0";
            }
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

        //execute all update functions in order, starting with the version AFTER the version we currently have
        foreach (CreateDatabase::$upgradeFunctionNames as $key => $funct) {
            if ($this->compareVersionNumbers($key, $dbVersion) > 0) {
                if (method_exists($this, $funct)) {
                    //call the upgrade function
                    $this->$funct();
                }
                $versionNumber = str_replace('db', '', $funct);
                $versionNumber = str_replace('_', '.', $versionNumber);
                DbManager::NonQuery("update app_version set version = '$versionNumber'");
            }
        }
        return true;
    }

    /**
     * Compares two version numbers by padding their numbers with zeros and comparing those strings
     * @param type $v1
     * @param type $v2
     * @return type
     */
    function compareVersionNumbers($v1, $v2) {
        $versionNumbers = [$v1, $v2];
        foreach ($versionNumbers as $key => $versionNumber) {
            $str = '';
            $parts = explode('.', $versionNumber);
            foreach ($parts as $part) {
                $str = $str . str_pad($part, 3, '0');
            }
            $versionNumbers[$key] = $str;
        }
        return strcmp($versionNumbers[0], $versionNumbers[1]);
    }

    function db0_1_0() {
        //log on as root and create the database
        $this->createVideoDatabase($this->rootUsername, $this->rootPassword, $this->dbHost);

        DbManager::NonQuery("
            create table app_version(
                version varchar(10)
                )");
        DbManager::NonQuery("insert into app_version(version) values('0.1.0')");

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
        DbManager::NonQuery(" 
            create table video_source(
               location varchar(200) not null primary key,
               base_url varchar(2000) not null,
               media_type varchar(10) not null,
               security_type varchar(20) not null,
               refresh_videos boolean default 0
        )");
        DbManager::NonQuery(" 
            create table watch_video(
                username varchar(128) not null,
                video_id int not null,
                time_in_seconds int(10) not null,
                date_watched datetime not null,
                primary key (username, video_id),
                foreign key (video_id) references video (video_id)
        )");
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
    }

    function db0_2_0() {
        include_once(dirname(__FILE__) . "/../Video.class.php");
        //delete any videos that are no longer on the filesystem but are still in the database
        Video::DeleteMissingVideos();
        //delete all videos that have previously acceptable invalid columns
        $videoIds = DbManager::SingleColumnQuery('select video_id from video where path is null or url is null or sd_poster_url is null or hd_poster_url is null');
        Queries::DeleteVideos($videoIds);

        DbManager::NonQuery('alter table video add column sd_poster_url varchar(767) unique not null');
        DbManager::NonQuery('alter table video add column hd_poster_url varchar(767) unique not null');
        DbManager::NonQuery('alter table video add column year int(4)');
        DbManager::NonQuery('alter table video drop column release_date');
        //update the table to no longer allow duplicates
        DbManager::NonQuery('alter table video modify column path varchar(767) unique not null');
        DbManager::NonQuery('alter table video modify column url varchar(767) unique not null');
    }

    function db0_2_2() {
        DbManager::NonQuery('alter table video drop index sd_poster_url');
        DbManager::NonQuery('alter table video drop index hd_poster_url');
    }

    function db0_3_0() {
        DbManager::NonQuery('alter table video_source drop primary key, add column id int not null auto_increment primary key');
    }

    function db0_3_9() {
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
                    v.year,
                    v.media_type,
                    v.video_source_path,
                    v.video_source_url,
                    t.writer,
                    t.director,
                    v.plot
                FROM video v, tv_episode t
                WHERE v.video_id = t.video_id");
    }

    function db0_3_17() {
        DbManager::NonQuery("alter table video add column date_added date not null");
        DbManager::NonQuery("alter table video add column date_modified date not null");
    }

    function db0_3_18() {
        $videoIds = DbManager::SingleColumnQuery("select video_id from video where path like '%.extra.%'");
        Queries::DeleteVideos($videoIds);
    }

    function db0_3_19() {
        DbManager::nonQuery("
            create table recently_watched(
                video_id int not null,
                username varchar(100) not null,
                date_watched datetime not null,
                foreign key (video_id) references video(video_id),
                primary key (video_id, username)
            );"
        );
    }

}

?>
