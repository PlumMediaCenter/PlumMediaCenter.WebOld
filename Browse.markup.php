<div class="video-container">
    <?php foreach ($videos as $video) { ?>
        <div class="video-tile">
            <a href="VideoInfo.php?videoId=<?php echo $video->videoId; ?>">
                <?php if ($video->posterModifiedDate === null) { ?>
                    <span class="noPosterText"><?php echo $video->title; ?></span>
                <?php } ?>
                <img class="poster" src="<?php echo $video->hdPosterUrl; ?>"/>
            </a>
        </div>
    <?php } ?>
</div>
<script type="text/javascript">
    $("#browseNav").addClass("active");
</script>
