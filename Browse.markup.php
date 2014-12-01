<?php if ($mediaType === null) { ?>
    <div class="tabbable">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#moviesPane" data-toggle="tab">Movies</a></li>
            <li><a href="#tvShowsPane" data-toggle="tab">Tv Shows</a></li>
        </ul>
        <div class="tab-content">
        <?php } ?>
        <?php if ($mediaType === null || $mediaType === Enumerations::MediaType_Movie) { ?>
            <div id="moviesPane" class="tab-pane active video-container">
                <h2>Movies</h2> 
                <?php
                foreach ($movies as $video) {
                    ?>
                    <div class="video-tile">
                        <?php if($video->hasPoster === false){?>
                        <span class="noPosterText"><?php echo $video->title; ?></span>
                        <?php }?>
                        </span>
                        <a href="VideoInfo.php?videoId=<?php echo $video->videoId; ?>"><img class="poster" src="<?php echo $video->hdPosterUrl; ?>"/></a>
                    </div>
                    <?php
                }
                ?>
            </div>
        <?php } ?>
        <?php if ($mediaType === null || $mediaType === Enumerations::MediaType_TvShow) { ?>
            <div id="tvShowsPane" class="tab-pane video-container">
                <h2>Tv Shows</h2>
                <?php
                foreach ($tvShows as $video) {
                    $modalId = "modal-" . md5($video->title);
                    ?>
                    <div class="video-tile" >
                         <?php if($video->hasPoster === false){?>
                        <span class="noPosterText"><?php echo $video->title; ?></span>
                        <?php }?>
                        <a href="VideoInfo.php?videoId=<?php echo $video->videoId; ?>"><img class="poster" src="<?php echo $video->hdPosterUrl; ?>"/></a>
                        </a>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <?php if ($mediaType === null) { ?>
        </div>
    </div>
<?php } ?>
<script type="text/javascript">
    $("#browseNav<?php echo $mediaType; ?>").addClass("active");
</script>
