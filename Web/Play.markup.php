<!--<script type="text/javascript" src="plugins/jwplayer/jwplayer.js"></script>-->
<script type="text/javascript" src="js/play.js"></script>
<script type="text/javascript" src="plugins/jwplayer6/jwplayer.js"></script>

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
            var videoList = <?php echo json_encode($videoList); ?>;
            var jwPlaylist = [
<?php
$comma = "";
foreach ($videoList as $video) {
    echo $comma;
    ?>
        {
        file: "<?php echo $video->url; ?>",
                image: "<?php echo $video->hdPosterUrl; ?>",
                title: "<?php echo $video->title; ?>"
        }
    <?php $comma = ",";
}
?>
    ];
</script>
<h2 id="playTitle" style="margin:0px; padding:0px;line-height:0px;width:100%;display:inline-block; font-size: 15px; text-align:center;"></h2>
<div id="videoPlayer" style="width:100%;">JW Player goes here</div>

