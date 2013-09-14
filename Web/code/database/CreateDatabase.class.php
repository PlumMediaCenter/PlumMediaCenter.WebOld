<?php

include_once(dirname(__FILE__) . "/../DbManager.class.php");

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
        //log in to the db as root and create the video database
        $this->createVideoDatabase($this->rootUsername, $this->rootPassword, $this->dbHost);
        //create all tables
        $this->table_video();
        $this->table_tv_episode();
        $this->table_video_source();
        $this->table_watch_video();
    }

    private function createVideoDatabase($rootUsername, $rootPassword, $host) {
        $user = config::$dbUsername;
        $pass = config::$dbPassword;
        $db = config::$dbName;

        try {
            $dbh = new PDO("mysql:host=$host", $rootUsername, $rootPassword);
            //delete any previous references to the user or the database
            $dbh->exec("delete from mysql.user where user = 'plumvideoplayer';");
            $dbh->exec("drop user 'plumvideoplayer'@'localhost';");
            $dbh->exec("drop database plumvideoplayer;");
            //create the database
            $dbh->exec("CREATE DATABASE `$db`;
                CREATE USER '$user'@'localhost' IDENTIFIED BY '$pass';
                GRANT ALL ON `$db`.* TO '$user'@'$host';
                FLUSH PRIVILEGES;") or die(print_r($dbh->errorInfo(), true));
        } catch (PDOException $e) {
            die("DB ERROR: " . $e->getMessage());
        }
    }

    private function table_video() {
        $table = new Table();
        DbManager::nonQuery("drop table video");
        $sql = "create table video(
                    video_id int not null auto_increment,
                    title char(100),
                    running_time int(5),
                    plot varchar(3000), 
                    path varchar(1000) not null,
                    url varchar(2000),
                    filetype char(15),
                    metadata_last_modified_date datetime,
                    poster_last_modified_date datetime,
                    mpaa char(200), 
                    release_date date, 
                    media_type char(10) not null, 
                    video_source_path varchar(2000) not null,
                    video_source_url varchar(2000) not null,
                    primary key(video_id)
                );";
        DbManager::nonQuery($sql);
    }

    private function table_tv_episode() {
        DbManager::nonQuery("drop table tv_episode");
        $sql = "   
            create table tv_episode(
                video_id int not null,
                tv_show_video_id int not null,
                season_number int,
                episode_number int,
                writer char(50),
                director char(50),
                primary key(video_id)
            );";
        DbManager::nonQuery($sql);
    }

    private function table_video_source() {
        DbManager::nonQuery("drop table video_source");
        $sql = "
            create table video_source(
            location char(200),
            base_url char(200),
            media_type char(10),
            security_type char(20),
            refresh_videos int(1) default 0,
            primary key(location)
        );";
        DbManager::nonQuery($sql);
    }

    private function table_watch_video() {
        DbManager::nonQuery("drop table watch_video");
        $sql = "
            create table watch_video(
                username char(128),
                video_id int not null,
                time_in_seconds int(10),
                position_in_bytes int(100),
                date_watched datetime,
                primary key (username, video_id)
            ); ";
        DbManager::nonQuery($sql);
    }

}

?>
