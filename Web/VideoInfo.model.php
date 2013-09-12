<?php

class VideoInfoModel extends Model {

    public $video;

}

/**
 * Spins through the list of tv shows and prints them as table rows. 
 * @param type $videoId 
 */
function printTvShowFileList($tvShow) {
    echo "<table>";
    //get the list of all episodes of this tv series. 
    $episodeList = $tvShow->episodes;

    $nextEpisodeId = TvShow::getNextEpisodeToWatch($tvShow->videoId);
    $currentSeasonNumber = -2;
    foreach ($episodeList as $episode) {
        $videoTitle = $episode->title;
        $episodeId = $episode->getVideoId();
        $episodeNumber = $episode->episodeNumber;
        $seasonNumber = $episode->seasonNumber;

        if ($seasonNumber != $currentSeasonNumber) {
            $currentSeasonNumber = $seasonNumber;
            //create a new row
            ?>
            <tr><td colspan="5">Season <?php echo $seasonNumber ?><td></tr>
            <?php
        }
        ?>
        <tr data-video-id="<?php echo $episodeId; ?>" id="episodeRow_<?php echo $episodeId; ?>" class="episodeRow <?php echo $nextEpisodeId == $episodeId ? "nextEpisodeRow" : ""; ?>" style="border:1px solid black;" episodeId="<?php echo $episodeId; ?>">
            <td class="transparent"><?php echo $episodeNumber; ?></td>
            <td class="transparent"><?php echo $episodeId; ?></td>
            <td class="transparent"><a class="playButton18" style="display:block;" href="WatchVideo.php?videoId=<?php echo $episodeId; ?>" title="Play"></a></td>
            <td class="transparent"><a class='addToPlaylistButton18' style="display:block;" href='#' onclick="addVideoToPlaylist($episodeId, '<?php echo $videoTitle; ?>');" title='Add Video To Playlist'></a></td>
            <td class="transparent"><a class='infoButton18' style="display:block;" ></a></td>
            <td class="transparent"><a href="Play.php?videoId=<?php echo $episodeId; ?>"><?php echo $videoTitle; ?></a></td>
            <td class="transparent"><div class="progressbar"><div class="percentComplete" style="width:<?php echo $episode->progressPercent();?>%">&nbsp;</div></div></a>
        </tr>
        <?php
    }
    echo "</table>";
}

/**
 * Spins through the list of tv shows and print them out as a grid of thumbnails
 * @param TvShow $tvShow 
 */
function printTvShowGridTiles($tvShow) {
    //get the list of all episodes of this tv series. 
    $episodeList = $tvShow->episodes;

    $currentSeasonNumber = -2;
    /* @var  $episode TvEpisode */
    foreach ($episodeList as $episode) {

        $episodeTitle = $episode->title;
        $episodeId = $episode->videoId;
        $episodeNumber = $episode->episodeNumber;
        $seasonNumber = $episode->seasonNumber;
        if ($seasonNumber != $currentSeasonNumber) {
            $currentSeasonNumber = $seasonNumber;
            ?>
            <br/>
            <div style="display:block; clear:both;">Season <?php echo $seasonNumber; ?></div>
            <?php
        }
        $playUrl = "Play.php?videoId=$episodeId";
        ?>
        <div id="episode<?php echo $episodeId; ?>" onclick_bak="window.location.href='<?php echo $playUrl; ?>'" class='gridTile' style="position:relative;padding:none; margin:none;"  episodeId="<?php echo $episodeId; ?>">
            <div id="episodeTile_<?php echo $episodeId; ?>"class="episodeTile">
                <div  class="halfTransparent posterCover" style="padding:none; margin:none;display:none; background-color:#2D2D2D; width:100%; height:100%;position:absolute;">
                </div>
                <img src="<?php echo $episode->hdPosterUrl; ?>"/>
                <span><br/><?php echo "$episodeNumber - $episodeTitle"; ?> </span>
                <a href="<?php echo $playUrl; ?>" title="Play <?php echo "$episodeTitle"; ?>" class="playButton semiTransparent"  style="position:absolute; left:35%; top:30%;"></a>
                <a class = 'infoButton18'  style="display:block;" onclick="getEpisodeInfo('<?php echo $episodeId; ?>', 'episodeTile_<?php echo $episodeId; ?>');
                        return false;"></a>

            </div>
        </div>
        <?php
    }

    //if we added at least 1 season, we need to end the last season added.
    if ($currentSeasonNumber != -2) {
        echo "</ul>";
    }
}
?>
