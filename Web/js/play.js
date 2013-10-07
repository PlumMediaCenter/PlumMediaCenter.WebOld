window.player = null;
$(document).ready(function() {
    jwplayer("videoPlayer").setup({
        flashplayer: "plugins/jwplayer/player.swf",
        file: videoUrl,
        image: posterUrl,
        autostart: true,
        events: {
            onComplete: function(eventName) {
                updateVideoPosition(videoId, -1, true);
            },
            onTime: onTime,
            onPlay: onPlay
        },
        provider: 'http'
    });

    //set the reference to the player object so we don't have to look for it again.
    window.player = jwplayer("videoPlayer");
});

var startVideoWhereWeLeftOffProcessed = false;
/**
 * Seeks to the playback position indicated by the database. This should only be called ONCE, 
 * and only after the video has started playing
 */
function startVideoWhereWeLeftOff() {
    //seek the player to the startPosition
    //if a startSeconds value greater than 0 was provided, seek to that position in the video
    if (startSeconds > 0) {
        player.seek(startSeconds);
    }
}
//keeps track of the number of seconds that have passed since the video has saved its position in the database
var playPositionUpdateTime = new Date();

function onPlay() {
    playPositionUpdateTime = new Date();
}

function onTime(obj) {
    if (startVideoWhereWeLeftOffProcessed === false && obj.position > 0) {
        startVideoWhereWeLeftOffProcessed = true;
        startVideoWhereWeLeftOff();
    }

    var positionInSeconds = obj.position;
    //every minute, update the database with the current video's play position
    var nowTime = new Date();
    var timeSinceLastUpdate = nowTime - playPositionUpdateTime;
    if (timeSinceLastUpdate > 4000) {
        playPositionUpdateTime = new Date();
        updateVideoPosition(videoId, positionInSeconds);
    }
}

function updateVideoPosition(videoId, seconds, bFinished) {
    bFinished = bFinished !== undefined ? bFinished : false;
    $.ajax('api/SetVideoProgress.php',
            {
                data: {
                    videoId: videoId,
                    seconds: seconds,
                    finished: bFinished
                }
            }
    );
}