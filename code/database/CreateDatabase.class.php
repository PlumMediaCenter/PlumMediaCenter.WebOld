<?php

include_once(dirname(__FILE__) . "/../DbManager.class.php");
include_once(dirname(__FILE__) . "/../controllers/VideoController.php");
include_once(dirname(__FILE__) . "/../functions.php");

/**
 * Creates the entire database
 */
class CreateDatabase
{

    private $rootUsername;
    private $rootPassword;
    private $dbHost;
    //this is a list of all db upgrade functions that are callable, in order. 
    private static $versions = [
        '0.1.00',
        '0.1.01',
        '0.1.02',
        '0.1.03',
        '0.1.04',
        '0.1.05',
        '0.1.06',
        '0.1.07',
        '0.1.08',
        '0.2.00',
        '0.2.01',
        '0.2.02',
        '0.3.00',
        '0.3.01',
        '0.3.02',
        '0.3.03',
        '0.3.04',
        '0.3.05',
        '0.3.06',
        '0.3.07',
        '0.3.08',
        '0.3.09',
        '0.3.10',
        '0.3.11',
        '0.3.12',
        '0.3.13',
        '0.3.14',
        '0.3.15',
        '0.3.16',
        '0.3.17',
        '0.3.18',
        '0.3.19',
        '0.3.20',
        '0.3.21',
        '0.3.22',
        '0.3.23',
        '0.3.24',
        '0.3.25',
        '0.3.26',
        '0.3.27',
        '0.3.28',
        '0.3.30',
        '0.3.31',
        '0.3.32',
        '0.3.33',
        '0.3.34',
        '0.3.35',
        '0.3.36'
    ];

    function __construct($rootUsername, $rootPassword, $dbHost)
    {
        $this->rootUsername = $rootUsername;
        $this->rootPassword = $rootPassword;
        $this->dbHost = $dbHost;
    }

    private function createVideoDatabase($rootUsername, $rootPassword, $host)
    {
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
    static function DatabaseIsUpToDate()
    {
        $cur = CreateDatabase::CurrentDbVersion();
        $latest = CreateDatabase::LatestDbVersion();
        return $cur == $latest;
    }

    /**
     * Returns the latest app version
     * @return string - the latest version possible of this application
     */
    static function LatestDbVersion()
    {
        return end(CreateDatabase::$versions);
    }

    /**
     * Returns the current app version in the database. 
     * @return string - the current app version in the database
     */
    static function CurrentDbVersion($dbHost = null, $dbUsername = null, $dbPassword = null)
    {
        $version = '0.0.0';
        $tableExists = DbManager::TableExists("app_version");
        //see if the version table exists. If it doesn't, then we start at the beginning.
        if ($tableExists === false) { } else {
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
    function upgradeDatabase()
    {
        $dbVersion = CreateDatabase::CurrentDbVersion($this->dbHost, $this->rootUsername, $this->rootPassword);

        //execute all update functions in order, starting with the version AFTER the version we currently have
        foreach (CreateDatabase::$versions as $version) {
            $functionName = $this->getVersionFunctionName($version);
            if ($this->compareVersionNumbers($version, $dbVersion) > 0) {
                if (method_exists($this, $functionName)) {
                    //call the upgrade function
                    $this->$functionName();
                }
                $versionNumber = str_replace('db', '', $functionName);
                $versionNumber = str_replace('_', '.', $versionNumber);
                DbManager::NonQuery("update app_version set version = '$versionNumber'");
            }
        }
        return true;
    }

    function getVersionFunctionName($version)
    {
        return "db" . str_replace(".", "_", $version);
    }

    /**
     * Compares two version numbers by padding their numbers with zeros and comparing those strings
     * @param type $v1
     * @param type $v2
     * @return type
     */
    function compareVersionNumbers($v1, $v2)
    {
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

    function db0_1_00()
    {
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

    function db0_2_00()
    {
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

    function db0_2_02()
    {
        DbManager::NonQuery('alter table video drop index sd_poster_url');
        DbManager::NonQuery('alter table video drop index hd_poster_url');
    }

    function db0_3_00()
    {
        DbManager::NonQuery('alter table video_source drop primary key, add column id int not null auto_increment primary key');
    }

    function db0_3_09()
    {
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

    function db0_3_17()
    {
        DbManager::NonQuery("alter table video add column date_added date not null");
        DbManager::NonQuery("alter table video add column date_modified date not null");
    }

    function db0_3_18()
    {
        $videoIds = DbManager::SingleColumnQuery("select video_id from video where path like '%.extra.%'");
        Queries::DeleteVideos($videoIds);
    }

    function db0_3_19()
    {
        DbManager::nonQuery("
            create table recently_watched(
                video_id int not null,
                username varchar(100) not null,
                date_watched datetime not null,
                foreign key (video_id) references video(video_id),
                primary key (video_id, username)
            );
        ");
    }

    function db0_3_34()
    {

        DbManager::NonQuery("
            create table user(
                user_id int auto_increment primary key,
                email_address varchar(254) not null,
                first_name varchar(100) not null,
                last_name varchar(100) not null
            );
        ");
        //create the default user
        DbManager::NonQuery("
            insert into user(user_id, email_address, first_name, last_name)
            values (1, 'DefaultUser@PlumMediaCenter.com', 'Default', 'User')
        ");
        //remove the username in favor of user id
        DbManager::nonQuery("alter table recently_watched drop username;");
        DbManager::nonQuery("alter table recently_watched add user_id int;");
        //prepopulate with user_id 1 (default user)
        DbManager::nonQuery("update recently_watched set user_id = 1;");
        
        DbManager::nonQuery("alter table watch_video drop username;");
        DbManager::nonQuery("alter table watch_video add user_id int;");
        DbManager::nonQuery("update watch_video set user_id = 1;");

        //prepopulate with user_id 1 (default user)
        DbManager::nonQuery("update recently_watched set user_id = 1;");
        //enforce non null user_id
        DbManager::nonQuery("
            alter table recently_watched
            add constraint constraint_unique_user_id unique(user_id);
        ");


        //add foreign key constraint for user_id
        DbManager::nonQuery("
            alter table recently_watched 
            add constraint fk_user_id foreign key(user_id) references user(user_id);
        ");

        DbManager::nonQuery("
            create table list(
                list_id int auto_increment primary key,
                name varchar(100) not null,
                user_id int not null,
                foreign key(user_id) references user(user_id)
            );
        ");

        //create the "My List" list for the default user
        Dbmanager::NonQuery("insert into list(list_id, name, user_id) values(1, 'My List', 1);");

        DbManager::nonQuery("
            create table list_item(
                list_item_id int auto_increment primary key,

                list_id int not null,
                foreign key(list_id) references list(list_id),

                video_id int not null,
                foreign key(video_id) references video(video_id),

                display_order int not null,

                date_created datetime default now()
            );
        ");
    }
}
