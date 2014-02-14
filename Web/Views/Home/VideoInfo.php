<?php include_once(basePath() . '/Code/TvShow.class.php'); ?>
<?php section("head"); ?>
<script type="text/javascript" src="<?php urlContent("~/Scripts/Home/VideoInfo.js"); ?>"></script>
<style type="text/css">
    .selected{
        background-color:grey;
        color:white;
    }
    .selected a, .selected a:visited{
        color:white
    }
</style>
<script type="text/javascript">
    var video = <?php json($model->video); ?>;
</script>
<?php endSection(); ?>
<div id="video-info-row" class="row">
    <div id="video-info-poster-col" class="col-md-3">
        <?php if ($model->video->mediaType == Enumerations::MediaType_TvEpisode) { ?>
            <a href="<?php urlAction("Home/VideoInfo", ['videoId' => $model->video->getTvShowVideoId()]); ?>">
                Back to Season: '<?php echo $model->video->showName; ?>'
            <?php } ?>
            <a href="<?php urlAction("Home/Play", ["videoId" => $model->video->videoId]); ?>" >
                <img id="video-info-poster" src="<?php echo $model->video->hdPosterUrl; ?>"/>
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
                    Rated <?php echo $model->video->mpaa; ?>
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
        if ($model->video->getMediaType() == Enumerations::MediaType_TvShow) {
            ?>
            <table class='table'>
                <thead>
                    <tr>
                        <th>Episode</th>
                        <th style='display:none;'>VID</th>
                        <th  style='display:none;'>Play</th>
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
                        $episodeNumber = $episode->episodeNumber;
                        $seasonNumber = $episode->seasonNumber;
                        $percentWatched = $episode->progressPercent();
                        $playUrl = getUrlAction('Home/Play') . "?videoId=$episodeId";
                        if ($seasonNumber != $currentSeasonNumber) {
                            $currentSeasonNumber = $seasonNumber;
                            //create a new row
                            ?>
                            <tr><td colspan="6">Season <?php echo $seasonNumber ?><td></tr>
                            <?php
                        }
                        ?>
                        <tr data-video-id="<?php echo $episodeId; ?>" id="episodeRow_<?php echo $episodeId; ?>" 
                            class="episodeRow <?php echo $nextEpisodeId == $episodeId ? "nextEpisodeRow" : ""; ?>" 
                            style="border:1px solid black;" episodeId="<?php echo $episodeId; ?>">
                            <td class="transparent"><?php echo $episodeNumber; ?></td>
                            <td class="transparent" style='display:none;'><?php echo $episodeId; ?></td>
                            <td class="transparent" style='display:none;'><a class="playButton18" style="display:block;" href="<?php echo $playUrl; ?>" title="Play">Play</a></td>
                            <!--<td class="transparent">  <a style="cursor:pointer;" onclick="$.getJSON('api/AddToPlaylist.php?playlistName=My Playlist&videoIds=<?php echo $episodeId; ?>');">+</a></td>-->
                            <td class="transparent"><a class="play" href="<?php echo $playUrl; ?>"><?php echo $videoTitle; ?></a></td>
                            <td class="transparent"><div class="progressbar">
                                    <div class="percentWatched" style="width:<?php echo $percentWatched; ?>%">
                                    </div>
                                    <div class="percentWatchedText"><?php echo $percentWatched; ?>%
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
    <div class="col-md-7" style="border:0px solid red;">
        <div id="episodeInfo" class="shadow">
            <h1 id="title" style="text-align:center;"></h1>
            <img align="right" id="episodePoster"/>
            <p>Season <span id="seasonNumber"></span> Episode <span id="episodeNumber"></span>
                <br/><b>Rating: </b><span id="mpaa"></span>
                <br/><b>Release Date:</b> <span id="year"></span>
            </p>
            <div id="plot"></div>
        </div>
    </div>
</div>


