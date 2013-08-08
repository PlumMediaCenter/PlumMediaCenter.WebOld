<?php

include_once("../DatabaseManager.class.php");

/**
 * Creates the entire database
 */
class CreateDatabase {

    private $dbMan;

    function __construct() {
        $this->dbMan = DbManager::getInstance();
        $this->table_video();
    }

    private function table_video() {
        $this->dbMan->nonQuery("drop table video");
        $sql = "create table video(
                    video_id int not null auto_increment,
                    video_title char(100),
                    running_time int(5),
                    overview varchar(3000), 
                    file_path varchar(1000) not null,
                    filetype char(15),
                    video_file_id int,
                    poster_file_id int,
                    poster_thumbnail_file_id int,
                    metadata_last_modified_date datetime,
                    poster_last_modified_date datetime,
                    content_rating char(200), 
                    date_first_released date, 
                    media_type char(10)  not null, 
                    primary key(video_id)
                );";
        $this->dbMan->nonQuery($sql);
    }

}

?>
