window.player = null;
$(document).ready(function() {
    jwplayer("videoPlayer").setup({
        //  file: videoUrl,
        // image: posterUrl,
        autostart: true,
        primary: "flash",
        provider: 'http',
        playlist: jwPlaylist,
//      listbar: {
//          position: 'right',
//          size: 320
//      },
        events: {
            onPlaylistItem: function(obj) {
                //mark the last video that was being watched as complete
                updateVideoPosition(previousVideoId(), -1, true);

                var video = videoList[obj.index];
                if (video.mediaType == enumerations.tvEpisode) {
                    var title =
                            "<a href='VideoInfo.php?videoId=" + video.videoId + "'>" +
                            video.showName + "</a>"
                            + " Season " + video.seasonNumber +
                            " Episode " + video.episodeNumber + " - " + video.title;
                    $("#playTitle").html(title);
                }
            },
            onComplete: function(eventName) {
                updateVideoPosition(currentVideoId(), -1, true);
            },
            onTime: onTime,
            onPlay: onPlay
        }

    });

    //set the reference to the player object so we don't have to look for it again.
    window.player = jwplayer("videoPlayer");
    //make the player full screen
    setTimeout(resizePlayer, 300);
    $(window).resize(resizePlayer);
});

/**
 * Gets the previous videoId, if one exists. Otherwise, returns -1
 * @returns {Number}
 */
function previousVideoId() {
    var idx = jwplayer().getPlaylistIndex();
    var video = videoList[idx - 1];
    var videoId = (video == undefined) ? -1 : video.videoId;
    return videoId;
}
function currentVideoId() {
    var idx = jwplayer().getPlaylistIndex();
    var videoId = videoList[idx].videoId;
    return videoId;
}

function resizePlayer() {
    //get the current width of the containerRelativer,set the jwplayer to that size
    var w = $("#containerRelativer").width();
    var h = displayHeight() - 40;
    player.resize(w, h);
}

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
    //every so often, update the database with the current video's play position
    var nowTime = new Date();
    var timeSinceLastUpdate = nowTime - playPositionUpdateTime;
    if (timeSinceLastUpdate > 4000) {
        playPositionUpdateTime = new Date();
        updateVideoPosition(currentVideoId(), positionInSeconds);
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