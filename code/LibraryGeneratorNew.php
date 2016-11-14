<?php

include_once('database/Queries.class.php');
include_once('database/Pager.php');

include_once('controllers/VideoController.php');
include_once('Models/GenerateLibraryResultModel.php');

include_once('Movie.class.php');

class LibraryGeneratorNew {

    const PAGE_SIZE = 50;

    /**
     *
     * @var GenerateLibraryResultModel
     */
    public $result;

    function generateLibrary() {
        $this->result = new GenerateLibraryResultModel();

        //process one source at a time
        $sources = Queries::GetVideoSources();
        foreach ($sources as $source) {
            if ($source->media_type === Enumerations::MediaType_Movie) {
                $this->processMovies($source);
            }
        }
        return $this->result;
    }

    private function processMovies($source) {
        $sourcePathLength = strlen($source->location);
        //get the list of videos from the filesystem
        $videoHash = getVideoHashFromDir($source->location);
        $deletedVideoIds = [];
        $pager = new Pager("*", "from video where video_source_path like '$source->location'", self::PAGE_SIZE);
        while ($pager->next()) {
            foreach ($pager->records as $video) {
                if (isset($video) === false) {
                    $k = 2;
                }
                //replace the source path in the video so the casing will match
                //$nonSourcePathPart = substr($video->path, $sourcePathLength);
                //$video->path = substr_replace($video->path, $source->location, 0) . $nonSourcePathPart;
                //if this video is already in the database, nothing more needs done. 
                if (isset($videoHash[$video->path])) {
                    unset($videoHash[$video->path]);
                } else {
                    //this video has been deleted from the filesystem.
                    $deletedVideoIds[] = $video->video_id;
                }
            }
            DbManager::NonQuery("delete from video where video_id in (" . implode(',', $deleteVideoIds) . ')');
            $deleteVideoIds = [];
            $this->result->movieRemoveCount += count($deletedVideoIds);
        }

        $videoPaths = array_keys($videoHash);
        $newVideoCount = count($videoPaths);
        $this->result->movieAddCount += $newVideoCount;

        if ($newVideoCount > 0) {
            //insert the new video records
            Movie::InsertMany($source->base_url, $source->location, $videoPaths, 1);
        }
    }

}

?>