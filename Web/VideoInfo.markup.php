<script type="text/javascript" src="js/VideoInfo.js"></script>
<script type="text/javascript">
    var video = <?php echo $videoJson; ?>;
</script>

<div class="row">
    <div class="span3"><img src="<?php echo $video->hdPosterUrl; ?>" style="float:left;">
    </div>
    <div class="span9">
        <h1><?php echo $video->title; ?></h1>
        Rating: <?php echo $video->mpaa; ?>
        Plot: <?php echo $video->plot; ?>
    </div>
</div>
<div class="row">
    <div class="span5" style="border:0px solid red;">
        <?php
        include_once("code/functions.php");
        printTvShowFileList($video);
        ?>
    </div>
    <div class="span7" style="border:0px solid red;">
        <div id="episodeInfo" class="shadow">
           <h1 id="title" style="text-align:center;"></h1>
           <h4 style="font-weight: normal; text-align:center;">    
                Season <span id="seasonNumber"></span> Episode <span id="episodeNumber"></span>
           </h4>
            <span id="mpaa" style="font-weight:bold;"></span>
            

            <div id="plot"></div>
            <div><br/> <b>Release Date:<b/> <span id="year"></span> </div>
        </div>
    </div>
</div>


