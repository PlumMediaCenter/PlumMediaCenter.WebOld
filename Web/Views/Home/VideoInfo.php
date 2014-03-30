<?php include_once(basePath() . '/Code/TvShow.class.php'); ?>
<?php section("head"); ?>

<?php
endSection();
section("scripts");
?>
<script type="text/javascript" src="<?php urlContent("~/Scripts/Home/VideoInfo.js"); ?>"></script>
<script type="text/javascript" src="<?php urlContent("~/Scripts/lib/jquery-hoverIntent/jquery.hoverIntent.minified.js"); ?>"></script>
<script type="text/javascript">
    var video = <?php json($model->video); ?>;
</script>
<?php endSection(); ?>
<div id="video-info-row" class="row">
    <div id="video-info-poster-col" class="col-md-3">
        <?php if ($model->video->mediaType == Enumerations\MediaType::TvEpisode) { ?>
            <a href="<?php urlAction("Home/VideoInfo", ['videoId' => $model->video->getTvShowVideoId()]); ?>">
                Back to Season: '<?php echo $model->video->showName; ?>'
            <?php } ?>
            <a href="<?php urlAction("Home/Play", ["videoId" => $model->video->videoId]); ?>" >
                <img id="video-info-poster" class="rounded" src="<?php echo $model->video->hdPosterUrl; ?>"/>
            </a>  <br/>
            <a href="<?php urlAction("Home/Play", ["videoId" => $model->video->videoId]); ?>" >
                <button id="videoInfoPlayVideoBtn" class="btn btn-primary">
                    <span class="glyphicon glyphicon-play"></span>
                    Play
                </button>
            </a>
    </div>
    <div id="video-info-col" class="col-md-6">
        <!-- Title -->
        <div class="row">
            <div class="col-md-12">
                <h1 class="center mless"><?php echo $model->video->title; ?></h1>
            </div>
        </div>
        <!-- Genres -->
        <div class="row">
            <div class="col-md-12">
                <p class="center bold">
                    <?php
                    $bul = "";
                    foreach ($model->video->genres as $genre) {
                        echo " $bul $genre ";
                        $bul = "&bull;";
                    }
                    ?>
                </p>
            </div>
        </div>
        <!-- Rating -->
        <div class="row">
            <div class="col-md-12">
                <p class="center">
                    <?php echo ($model->video->year !== "0000-00-00") ? $model->video->year : ""; ?>
                    <?php echo $model->video->mpaa; ?>
                </p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?php echo $model->video->plot; ?>
            </div>
        </div>
    </div>    
    <div class="col-md-3"></div>
</div>
<div class="row">
    <div class="col-md-5" style="border:0px solid red;">
        <?php
        if ($model->video->getMediaType() == Enumerations\MediaType::TvShow) {
            ?>
            <table id="videoInfoEpisodeTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="episodeNumberWidth">#</th>
                        <th style='display:none;'>VID</th>
                        <th class="playButtonWidth">Play</th>
                        <th style='display:none;'>Add To Playlist</th>
                        <th>Title</th><th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    //get the list of all episodes of this tv series. 
                    $episodeList = $model->video->episodes;

                    $e = TvShow::GetNextEpisodeToWatch($model->video->videoId);
                    //if e is false, then there is no next episode to watch. 
                    $nextEpisodeId = ($e != false) ? $e->videoId : -1;
                    $currentSeasonNumber = -2;
                    foreach ($episodeList as $episode) {
                        $videoTitle = $episode->title;
                        $episodeId = $episode->getVideoId();
                        $seasonNumber = $episode->seasonNumber;
                        $episodeNumber = $episode->episodeNumber;
                        $percentWatched = $episode->progressPercent();
                        $percentWatched = 80;
                        $playUrl = getUrlAction('Home/Play') . "?videoId=$episodeId";
                        if ($seasonNumber != $currentSeasonNumber) {
                            $currentSeasonNumber = $seasonNumber;
                            //create a new row
                            ?>
                            <tr><td colspan="6">Season <?php echo $seasonNumber ?><td></tr>
                            <?php
                        }
                        ?>
                        <tr id="episodeRow_<?php echo $episodeId; ?>"                             
                            class="episodeRow <?php echo $nextEpisodeId == $episodeId ? "nextEpisodeRow" : ""; ?>" 
                            data-video-id="<?php echo $episodeId; ?>"  
                            data-season-number="<?php echo $seasonNumber; ?>"
                            data-episode-number="<?php echo $episodeNumber; ?>"
                            >
                            <td><?php echo $episodeNumber; ?></td>
                            <td class="hide"><?php echo $episodeId; ?></td>
                            <td  class="playButtonWidth"><a class="btn btn-primary" href="<?php echo $playUrl; ?>">
                                    <span class="glyphicon glyphicon-play"></span>
                                </a>
                            </td>
                            <!--<td class="transparent">  <a style="cursor:pointer;" onclick="$.getJSON('api/AddToPlaylist.php?playlistName=My Playlist&videoIds=<?php echo $episodeId; ?>');">+</a></td>-->
                            <td class="transparent"><?php echo $videoTitle; ?></td>
                            <td>
                                <div class="progressbarContainer">
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $percentWatched; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percentWatched; ?>%;">
                                            <span class="sr-only"><?php echo $percentWatched; ?>%</span>
                                        </div>
                                        <span class="percent"><?php echo $percentWatched; ?>%</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <div class="col-md-7" >
        <div id="episodeInfo" class="hide">

        </div>
    </div>
</div>


