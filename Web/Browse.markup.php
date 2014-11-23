<?php if ($mediaType === null) { ?>
    <div class="tabbable">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#moviesPane" data-toggle="tab">Movies</a></li>
            <li><a href="#tvShowsPane" data-toggle="tab">Tv Shows</a></li>
        </ul>
        <div class="tab-content">
        <?php } ?>
        <?php if ($mediaType === null || $mediaType === Enumerations::MediaType_Movie) { ?>
            <div id="moviesPane" class="tab-pane active">
                <h2>Movies</h2> 
                <?php
                foreach ($movies as $movie) {
                    ?>
                    <div class="tile">
                        <span>
                            <a title='Play Movie' href="Play.php?videoId=<?php echo $movie->videoId; ?>" title="View movie information"><?php echo $movie->title; ?></a>
                            <!--<a style="cursor:pointer;" onclick="addToPlaylist(<?php echo $movie->videoId; ?>);" title="Add to a playlist">+</a>-->
                        </span>
                        <a href="VideoInfo.php?videoId=<?php echo $movie->videoId; ?>"><img class="tileImg" src="<?php echo $movie->hdPosterUrl; ?>"/></a>
                    </div>
                    <?php
                }
                ?>
            </div>
        <?php } ?>
        <?php if ($mediaType === null || $mediaType === Enumerations::MediaType_TvShow) { ?>
            <div id="tvShowsPane" class="tab-pane">
                <h2>Tv Shows</h2>
                <?php
                foreach ($tvShows as $tvShow) {
                    $modalId = "modal-" . md5($tvShow->title);
                    ?>
                    <div class="tile" >
                        <span>
                            <a title='Play Tv Show' href="Play.php?playType=series&videoId=<?php echo $tvShow->videoId; ?>"><?php echo $tvShow->title; ?></a>
                            <!--<a style="cursor:pointer;" onclick="addToPlaylist(<?php echo $tvShow->videoId; ?>);" title="Add to a playlist">+</a>-->
                        </span>

                        <a href="VideoInfo.php?videoId=<?php echo $tvShow->videoId; ?>"><img src="<?php echo $tvShow->hdPosterUrl; ?>"/></a>
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
