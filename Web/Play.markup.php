<script type="text/javascript" src="plugins/jwplayer/jwplayer.js"></script>
<script type="text/javascript">
    var player;
    var videoUrl = "<?php echo $videoUrl; ?>";
    var posterUrl = "<?php echo $posterUrl;?>";

    $(document).ready(function() {
        jwplayer("videoPlayer").setup({
            flashplayer: "plugins/jwplayer/player.swf",
            file: videoUrl,
            image: posterUrl,
            autostart: true,
            events: {
                onComplete: function() {

                }
                //,onTime: currentTimeFunction
            },
            provider: 'http'
        });

        //set the reference to the player object so we don't have to look for it again.
        player = jwplayer("videoPlayer");
    });

</script>

<div  id="videoPlayer">JW Player goes here</div>