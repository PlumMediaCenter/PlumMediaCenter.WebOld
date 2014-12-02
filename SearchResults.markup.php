<div class="video-container">
    <?php foreach ($videos as $video) { ?>
        <div class="video-tile">
            <?php if (isset($video->hasPoster) && $video->hasPoster === false) { ?>
                <span class="noPosterText"><?php echo $video->title; ?></span>
            <?php } ?>
            <a href="VideoInfo.php?videoId=<?php echo $video->videoId; ?>"><img class="poster" src="<?php echo $video->hdPosterUrl; ?>"/></a>
        </div>
    <?php } ?>  
</div>
