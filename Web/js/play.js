$(document).ready(function() {
    jwplayer("videoPlayer").setup({
        flashplayer: "plugins/jwplayer/player.swf",
        file: videoUrl,
        image: posterUrl,
        autostart: true,
        events: {
            onComplete: function() {

            },
            onTime: onTime,
            onPlay: onPlay

        },
        provider: 'http'
    });

    //set the reference to the player object so we don't have to look for it again.
    player = jwplayer("videoPlayer");
});
//keeps track of the number of seconds that have passed since the video has saved its position in the database
var playPositionUpdateTime = new Date();

function onPlay() {
    playPositionUpdateTime = new Date();
}

function onTime(obj) {

    var durationInSeconds = obj.duration;
    var positionInSeconds = obj.position;
    //every minute, update the database with the current video's play position
    var nowTime = new Date();
    var timeSinceLastUpdate = nowTime - playPositionUpdateTime;
    if (timeSinceLastUpdate > 4000) {
        playPositionUpdateTime = new Date();
        updateVideoPosition(videoId, positionInSeconds);
    }

}

function updateVideoPosition(videoId, seconds) {
    $.ajax('ajax/SetVideoProgress.php',
            {
                data: {
                    videoId: videoId,
                    seconds: seconds
                }
            }
    );
}