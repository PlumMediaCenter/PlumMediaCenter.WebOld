<script type="text/javascript" src="js/VideoInfo.js"></script>
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
    var video = <?php echo $videoJson; ?>;
</script>

<div id="video-info-row" class="row">
    <div id="video-info-poster-col" class="col-md-3">
        <a href="Play.php?videoId=<?php echo $video->videoId; ?>" >
            <img id="video-info-poster" src="<?php echo $video->hdPosterUrl; ?>"/>
        </a>
    </div>
    <div id="video-info-col" class="col-md-6">
        <!-- Title -->
        <div class="row">
            <div class="col-md-12">
                <h1 class="center mless"><?php echo $video->title; ?></h1>
            </div>
        </div>
        <!-- Genres -->
        <div class="row">
            <div class="col-md-12">
                <p class="center bold">
                    <?php
                    $bul = "";
                    foreach ($video->genres as $genre) {
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
                   Rated <?php echo $video->mpaa; ?>
                </p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?php echo $video->plot; ?>
            </div>
        </div>
    </div>    
    <div class="col-md-3"></div>
</div>
<div class="row">
    <div class="col-md-5" style="border:0px solid red;">
        <?php
        include_once("code/functions.php");
        printTvShowFileList($video);
        ?>
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


