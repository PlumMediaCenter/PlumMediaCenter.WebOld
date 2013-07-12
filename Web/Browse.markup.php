<div class="tabbable">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#moviesPane" data-toggle="tab">Movies</a></li>
        <li><a href="#tvShowsPane" data-toggle="tab">Tv Shows</a></li>
    </ul>
    <div class="tab-content">
        <div id="moviesPane" class="tab-pane active">
            <h2>Movies</h2>
            <?php
            foreach ($movies as $movie) {
                ?>
                <div class="tile">
                    <img style="width:100%;" src="<?php echo $movie->hdPosterUrl; ?>"/>
                </div>
                <?php
            }
            ?>
        </div>
        <div id="tvShowsPane" class="tab-pane">
            <h2>Tv Shows</h2>
            <?php
            foreach ($tvShows as $tvShow) {
                $modalId = "modal-" . md5($tvShow->title);
                ?>
                <a class="tile" href="#<?php echo $modalId; ?>" role="button" data-toggle="modal">
                    <img src="<?php echo $tvShow->hdPosterUrl; ?>"/>
                </a>
                <div id="<?php echo $modalId; ?>" class="modal hide">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                        <h3 id="myModalLabel"><?php echo $tvShow->title; ?></h3>
                    </div>
                    <?php
                    foreach ($tvShow->seasons as $season) {
                        foreach ($season as $episode) {
                            ?>
                            <div class="tile">
                                <img src="<?php echo $episode->hdPosterUrl; ?>"/>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    $("#browseNav").addClass("active");
</script>
