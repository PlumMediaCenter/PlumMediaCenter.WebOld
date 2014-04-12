<?php section("head");?>
<style type="text/css">
    body{
        background-color: white;
    }
    #mainNavbar{
        display:none;
    }
</style>
<?php endSection();?>
<?php section("scripts");?>
<script type="text/javascript" src="<?php urlContent("~/Scripts/Home/play.js"); ?>"></script>
<script type="text/javascript" src="<?php urlContent("~/Scripts/lib/jwplayer6/jwplayer.js"); ?>"></script>
<script type="text/javascript" src="<?php urlContent("~/Scripts/lib/screenfull/screenfull.min.js"); ?>"></script>


<script type="text/javascript">
    var playType = "<?php echo $model->playType; ?>";
    var firstVideo = <?php echo json_encode($model->video); ?>;
    var playlistName = "<?php echo $model->playlistName; ?>";
    var startSeconds = <?php echo $model->video->videoStartSeconds(); ?>;

    var jwPlaylist = [{
            file: firstVideo.url,
            image: firstVideo.hdPosterUrl,
            title: firstVideo.title,
            video: firstVideo
        }];

</script>
<?php endSection();?>
<div id="backToBrowse">
    <h2 id="playTitle" style=""></h2>
</div>
<div id="videoPlayer" style="width:100%;">JW Player goes here</div>
<div id="playbackFinished"><h1>Playback has finished. Please select another video to watch.</h1></div>

