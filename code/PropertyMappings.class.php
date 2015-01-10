<?php

include_once(dirname(__FILE__) . '/Enumerations.class.php');

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PropertyMappings
 *
 * @author bplumb
 */
class PropertyMappings {

    static $videoMapping = [
        "media_type" => "mediaType",
        "sd_poster_url" => "sdPosterUrl",
        "hd_poster_url" => "hdPosterUrl",
        "video_id" => ["name" => "videoId", "dataType" => "integer"],
        "running_time_seconds" => ["name" => "runtime", "dataType" => "integer"],
        "poster_last_modified_date" => "posterModifiedDate",
        "year" => ["name" => "year", "dataType" => "integer"],
        "video_source_url" => null,
        "video_source_path" => null,
        "metadata_last_modified_date" => null,
        "path" => null
    ];
    static $episodeMapping = [
        "tv_show_video_id" => "tvShowVideoId",
        "season_number" => ["name" => "seasonNumber", "dataType" => "integer"],
        "episode_number" => ["name" => "episodeNumber", "dataType" => "integer"]
    ];
    static $videoSourceMapping = [
        "id" => ["name" => "id", "dataType" => "integer"],
        "base_url" => "baseUrl",
        "media_type" => "mediaType",
        "security_type" => "securityType",
        "refresh_videos" => null
    ];

    static function MapOne($row, $mapping) {
        $arr = PropertyMappings::MapMany([$row], $mapping);
        return $arr[0];
    }

    static function MapMany($rows, $mapping) {
        $results = [];
        foreach ($rows as $row) {
            $newRow = [];
            foreach ($row as $columnName => $value) {
                $newColumnName = $columnName;
                if (array_key_exists($columnName, $mapping)) {
                    $colMapping = $mapping[$columnName];
                    if (is_array($colMapping)) {
                        $newColumnName = $colMapping["name"];
                        if ($colMapping["dataType"] === 'integer') {
                            //converting null to int results in zero, which is NOT the same as null 
                            if ($value != null) {
                                $value = intval($value);
                            }
                        }
                    } else {
                        $newColumnName = $colMapping;
                        //if the column is set to null, skip this column entirely
                        if ($newColumnName == null) {
                            continue;
                        }
                    }
                }
                $newRow[$newColumnName] = $value;
            }
            $results[] = (object) $newRow;
        }
        return $results;
    }

}
