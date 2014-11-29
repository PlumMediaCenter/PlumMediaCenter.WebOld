<?php
foreach ($videos as $video) {
    if ($video->mediaType == Enumerations::MediaType_Movie) {
        ?>
        <div class="tile">
            <span>
                <a title='Play Movie' href="Play.php?videoId=<?php echo $video->videoId; ?>" title="View movie information"><?php echo $video->title; ?></a>
                <!--<a style="cursor:pointer;" onclick="addToPlaylist(<?php echo $video->videoId; ?>);" title="Add to a playlist">+</a>-->
            </span>
            <a href="VideoInfo.php?videoId=<?php echo $video->videoId; ?>"><img class="tileImg" src="<?php echo $video->hdPosterUrl; ?>"/></a>
        </div>
    <?php } else if ($video->mediaType == Enumerations::MediaType_TvShow) {
        ?>
        <div class="tile" >
            <span>
                <a title='Play Tv Show' href="Play.php?playType=series&videoId=<?php echo $video->videoId; ?>"><?php echo $video->title; ?></a>
                <!--<a style="cursor:pointer;" onclick="addToPlaylist(<?php echo $video->videoId; ?>);" title="Add to a playlist">+</a>-->
            </span>

            <a href="VideoInfo.php?videoId=<?php echo $video->videoId; ?>"><img src="<?php echo $video->hdPosterUrl; ?>"/></a>
        </a>
        </div>
    <?php } else if ($video->mediaType == Enumerations::MediaType_TvEpisode) {
        ?>
        <div class="tile" >
            <span>
                <a title='Play Tv Show' href="Play.php?playType=series&videoId=<?php echo $video->videoId; ?>"><?php echo $video->getFullEpisodeName(); ?></a>
                <!--<a style="cursor:pointer;" onclick="addToPlaylist(<?php echo $video->videoId; ?>);" title="Add to a playlist">+</a>-->
            </span>

            <a href="VideoInfo.php?videoId=<?php echo $video->videoId; ?>"><img src="<?php echo $video->hdPosterUrl; ?>"/></a>
        </a>
        </div>
        <?php
    }
}
?>