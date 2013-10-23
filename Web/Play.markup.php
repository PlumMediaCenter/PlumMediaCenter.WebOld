<a href="VideoInfo.php?videoId=<?php echo $videoId; ?>">Back to info</a>
<script type="text/javascript">
    var player;
    var videoId = <?php echo $videoId; ?>;
    var videoUrl = "<?php echo $videoUrl; ?>";
    var posterUrl = "<?php echo $posterUrl; ?>";
    var startSeconds = <?php echo $startSeconds; ?>;
//    $("#bodyPadding").hover(function() {
//        $(".navbar").css("visibility", "visible");
//    }, function() {
//        $(".navbar").mouseout(function() {
//            $(".navbar").css("visibility", "hidden");
//        });
//    });
</script>
<script type="text/javascript" src="plugins/jwplayer/jwplayer.js"></script>
<script type="text/javascript" src="js/play.js"></script>
<style type="text/css">

</style>


<div id="videoPlayer" style="width:100%;">JW Player goes here</div>
