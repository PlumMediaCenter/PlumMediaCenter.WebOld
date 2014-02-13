<?php
include_once(basePath() . '/Code/Movie.class.php');
include_once(basePath() . '/Code/TvShow.class.php');
include_once(basePath() . '/Code/TvEpisode.class.php');

class MetadataManagerController {

    function Index($mediaType = null) {
        include_once(basePath() . "/Models/MetadataManager/MetadataManagerModel.php");
        $model = new MetadataManagerModel($mediaType);
        return view($model);
    }

    function GeneratePosters($baseUrl, $basePath, $fullPath, $mediaType) {
        $v = new $mediaType($baseUrl, $basePath, $fullPath);
        //generate the posters
        $sdPosterSuccess = $v->generateSdPoster();
        $hdPosterSuccess = $v->generateHdPoster();
        $success = $sdPosterSuccess && $hdPosterSuccess;
        if ($success == false) {
            $this->result(false);
        }
        return $this->result($success, $baseUrl, $basePath, $fullPath, $mediaType);
    }

    function FetchMetadata($baseUrl, $basePath, $fullPath, $mediaType) {
        $v = new $mediaType($baseUrl, $basePath, $fullPath);
        $success = $v->fetchMetadata();
        return $this->result($success, $baseUrl, $basePath, $fullPath, $mediaType);
    }

    function FetchPoster($baseUrl, $basePath, $fullPath, $mediaType) {
        $v = new $mediaType($baseUrl, $basePath, $fullPath);
        $success = $v->fetchPoster();
        return $this->result($success, $baseUrl, $basePath, $fullPath, $mediaType);
    }

    function ReloadMetadata($baseUrl, $basePath, $fullPath, $mediaType) {
        $v = new $mediaType($baseUrl, $basePath, $fullPath);
        $success = $v->writeToDb();
        return $this->result($success, $baseUrl, $basePath, $fullPath, $mediaType);
    }

    function FetchAndGeneratePosters($baseUrl, $basePath, $fullPath, $mediaType) {
        $v = new $mediaType($baseUrl, $basePath, $fullPath);
        $success = $v->fetchPoster();
        if ($success === false) {
            return $this->result(false);
        }
        return $this->GeneratePosters($baseUrl, $basePath, $fullPath, $mediaType);
    }

    private function result($success, $baseUrl = null, $basePath = null, $fullPath = null, $mediaType = null) {
        $result = (object) [];
        $result->success = $success;
        //return the new video data to be put into the 
        $video = new $mediaType($baseUrl, $basePath, $fullPath);
        //load the latest metadata from the file into the video 
        $video->loadMetadata(true);
        $result->output = $this->getVideoMetadataRow($video);
        return json($result);
    }

    public static function GetVideoMetadataRow($v) {
        ob_start();
        $vSuccess = $v->nfoFileExists() && $v->posterExists() && $v->sdPosterExists() && $v->hdPosterExists();
        $txtSuccess = $vSuccess === true ? "true" : "false";
        ?>
        <tr style="cursor:pointer;" data-complete="<?php echo $txtSuccess; ?>" class="videoRow <?php echo $vSuccess ? "success" : "error"; ?>" mediatype="<?php echo $v->getMediaType(); ?>" baseurl="<?php echo htmlspecialchars($v->getVideoSourceUrl()); ?>" basepath="<?php echo htmlspecialchars($v->getVideoSourcePath()); ?>" fullpath="<?php echo htmlspecialchars($v->getFullPath()); ?>">
            <?php if ($v->getMediaType() == Enumerations::MediaType_TvEpisode) { ?>
                <td><?php echo $v->showName; ?></td>
            <?php } ?>
            <td><?php echo $v->title; ?></td>
            <td><?php echo $v->nfoFileExists() ? color("Yes", "green") : color("No", "red"); ?></td>
            <td><?php echo $v->posterExists() ? color("Yes", "green") : color("No", "red"); ?></td>
            <td><?php echo $v->sdPosterExists() ? color("Yes", "green") : color("No", "red"); ?></td>
            <td><?php echo $v->hdPosterExists() ? color("Yes", "green") : color("No", "red"); ?></td>
        </tr>
        <?php
        $row = ob_get_contents();
        ob_end_clean();
        return $row;
    }

}
