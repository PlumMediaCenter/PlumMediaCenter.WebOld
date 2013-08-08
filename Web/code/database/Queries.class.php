<?php

include_once(dirname(__FILE__) . "/../DbManager.class.php");

class Queries {

    private static $stmtInsertVideo = null;

    public static function insertVideo($title, $filePath, $filetype, $mediaType) {
        $pdo = DbManager::getPdo();
        if (Queries::$stmtInsertVideo == null) {
            $sql = "insert into video(video_title, file_path, filetype, media_type)" .
                    " values(:videoTitle, :filePath, :filetype, :mediaType)";
            $stmt = $pdo->prepare($sql);
            Queries::$stmtInsertVideo = $stmt;
        }
        $stmt = Queries::$stmtInsertVideo;
        $stmt->bindParam(":videoTitle", $title);
        $stmt->bindParam(":filePath", $filePath);
        $stmt->bindParam(":filetype", $filetype);
        $stmt->bindParam(":mediaType", $mediaType);
        Queries::$stmtInsertVideo->execute();
    }

    /**
     * deletes all videos from the video table. 
     */
    public static function truncateTableVideo() {
        $pdo = DbManager::getPdo();
        $sql = "truncate table video";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }

}

?>
