<?php section("scripts"); ?>
<?php endSection(); ?>
<div id="video-container">
    <?php
    foreach ($model->videos as $video) {
        if ($video->mediaType() == Enumerations\MediaType::Movie) {
            ?>
            <div class="video-tile">
                <span>
                    <!--<a title='Play Movie' href="Play.php?videoId=<?php echo $video->videoId(); ?>" title="View movie information"><?php echo $video->title(); ?></a>-->
                    <!--<a style="cursor:pointer;" onclick="addToPlaylist(<?php echo $video->videoId(); ?>);" title="Add to a playlist">+</a>-->
                </span>
                <a href="<?php urlAction("Home/VideoInfo");?>?videoId=<?php echo $video->videoId(); ?>"><img class="poster" src="<?php echo $video->posterUrl(); ?>"/></a>
            </div>
            <?php
        } else {
            ?>
            <div class="video-tile">
                <span>
                    <!--<a title='Play Tv Show' href="Play.php?playType=series&videoId=<?php echo $video->videoId(); ?>"><?php echo $video->title(); ?></a>-->
                    <!--<a style="cursor:pointer;" onclick="addToPlaylist(<?php echo $video->videoId; ?>);" title="Add to a playlist">+</a>-->
                </span>
                <a href="<?php urlAction("Home/VideoInfo");?>?videoId=<?php echo $video->videoId(); ?>"><img class="poster" src="<?php echo $video->posterUrl(); ?>"/></a>
                </a>
            </div>
            <?php
        }
    }
    ?>

</div>