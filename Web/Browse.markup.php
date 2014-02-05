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
                <!--<a class="tile" href="#<?php echo $modalId; ?>" role="button" data-toggle="modal">-->
                    <div class="tile" >
                        <span>
                            <a title='Play Tv Show' href="Play.php?playType=series&videoId=<?php echo $tvShow->videoId; ?>"><?php echo $tvShow->title; ?></a>
                            <!--<a style="cursor:pointer;" onclick="addToPlaylist(<?php echo $tvShow->videoId; ?>);" title="Add to a playlist">+</a>-->
                        </span>

                        <a href="VideoInfo.php?videoId=<?php echo $tvShow->videoId; ?>"><img src="<?php echo $tvShow->hdPosterUrl; ?>"/></a>
                        </a>
                    </div>
                    <div id="<?php echo $modalId; ?>" class="modal hide fade">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                            <h3 id="myModalLabel"><?php echo $tvShow->title; ?></h3>
                        </div>
                        <div class="modal-body">
                            <?php
                            $seasons = $tvShow->getSeasons();
                            foreach ($seasons as $key => $season) {
                                $seasonNum = -1;
                                //get the season number
                                if (count($season) > 0) {
                                    foreach ($season as $episode) {
                                        $seasonNum = $episode->seasonNumber;
                                        break;
                                    }
                                }
                                ?>
                                <h2><?php echo "Season $seasonNum";
                                ?> </h2>
                                <?php
                                foreach ($season as $episode) {
                                    ?>
                                    <div class="tile">
                                        <span style='font-size: 20px;'><?php echo "$episode->episodeNumber. $episode->title"; ?></span>
                                       <!--<a href="Play.php?videoId=<?php echo $episode->videoId; ?>"><img src="<?php echo $episode->hdPosterUrl; ?>"/></a>-->
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>

                    </div>
                    <?php
                }
                ?>
            </div>
        <?php } ?>
        <?php if ($mediaType === null) { ?>
        </div>
    </div>
<?php } ?>
<script type="text/javascript">
    $("#browseNav<?php echo $mediaType; ?>").addClass("active");
    $('.modal').on('show', function() {
//        //temporarily hide the scrollbars
//        $('body').css('overflow', 'hidden');
//
//        $(this).css({
//            width: 'auto',
//            left: "20px",
//            'margin-left': '0px',
//            top: "180px",
//            right: '20px',
//            'margin-top': '0!important',
//            height: 'auto',
//            'min-height': 'auto'
//        });
//        $(".modal-body").css({
//            height: '100%',
//            'max-height': 'inherit'
//        })
    });
    $('.modal').on('hide', function() {
        //show the scrollbars again
//        $('body').css('overflow', 'auto');
    });
</script>
