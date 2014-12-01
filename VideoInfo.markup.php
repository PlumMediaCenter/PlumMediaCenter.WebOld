<script type="text/javascript" src="js/VideoInfo.js"></script>
<script type="text/javascript" src="lib/lightbox/js/lightbox.min.js"></script>
<link rel="stylesheet" href="lib/lightbox/css/lightbox.css" />

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
<div class="row marginless" style="margin-top:5px;">
    <div class="col-md-3 text-center">
        <div id="videoInfoMainPosterContainer">
            <a href="<?php echo $video->getPosterUrl(); ?>" data-lightbox="poster">
                <img src="<?php echo $video->hdPosterUrl; ?>" id="videoInfoMainPoster" >
            </a>
        </div>
        <a id="videoInfoPlayBtn" href="Play.php?videoId=<?php echo $video->videoId; ?>" class="btn btn-primary">
            <span class="glyphicon glyphicon-play"></span>&nbsp;Play
        </a>
    </div>
    <div class="col-md-9">
        <h1 class="text-center"><?php echo $video->title; ?></h1>
        Rating: <?php echo $video->mpaa; ?>
        <br/> <br/><?php echo $video->plot; ?>
    </div>
</div>
<br/>
<div class="row marginless">
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


