//a reference to the jwplayer object created during page load
var player;
var previousPlaylistIndex = 0;
var currentPlaylistIndex = 0;
$(document).ready(function() {
    
    //display the video title on the page
    $(document).keydown(keyboardShortcuts);
    jwplayer("videoPlayer").setup({
        //file: "",
        // image: "",
        autostart: true,
        //default to try the html5 video mode first, then fallback to flash
        primary: "html5",
        playlist: jwPlaylist,
        startparam: "start",
        wmode : 'transparent',
        //      listbar: {
        //          position: 'right',
        //          size: 320
        //      },
        events: {
            //fired every time the playlist index changes
            onPlaylistItem: function(obj) {
                var video = player.getPlaylist()[obj.index].video;
                displayVideoTitle(video);
                //keep track of which index was the previous index
                previousPlaylistIndex = currentPlaylistIndex;
                currentPlaylistIndex = obj.index;
            },
            onComplete: function(eventName) {
                //tell the db that this video has just been finished
                updateVideoPosition(getCurrentVideo().videoId, -1, true);
                //if we are in playlist mode, tell the db to remove this video from the playlist
                if (playType === enumerations.PlayType_Playlist) {
                    updatePlaylistItemFinished(playlistName, getCurrentVideo().videoId);
                }
                //play the next video, if one exists
                playNextVideo();
            },
            onTime: onTime,
            onPlay: onPlay
        }

    });
    //set the reference to the player object so we don't have to look for it again.
    player = jwplayer("videoPlayer");
    //anytime the window is resized, resize the player accordingly
    $(window).resize(resizePlayer);

    /**
     * Returns the currently playing video object
     * @returns video object
     */
    function getCurrentVideo() {
        var playlist = player.getPlaylist();
        var idx = player.getPlaylistIndex();
        var video = playlist[idx].video;
        return video;
    }

    /**
     * Go to the server and find the next video that should be watched after this one
     * @param {type} callback
     * @returns {getNextVideo}
     */
    function getNextVideo(callback) {
        //if callback is undefined, just call an empty function
        callback = (callback == undefined) ? function() {
        } : callback;
        //if this is a playlist, get the next item and play it.
        if (playType === enumerations.PlayType_Playlist) {
            $.getJSON("api/GetNextPlaylistItem.php", {playlistName: playlistName}, function(video) {
                if (video !== false) {
                    callback(video);
                } else {
                    callback(undefined);
                }
            });
        } else if (playType === enumerations.PlayType_TvShow) {
            $.getJSON("api/GetNextEpisode.php", {videoId: getCurrentVideo().videoId}, function(video) {
                if (video !== false) {
                    callback(video);
                } else {
                    callback(undefined);
                }
            });
        }
    }

    /**
     * Call this whenever a video completes playing or a user wants to skip to the next item.
     * This will determine if we need to fetch a new video from the server or if we can 
     * use the playlist that we have generated thus far.
     * @returns {undefined}
     */
    function playNextVideo() {
        var playlist = player.getPlaylist();
        var playlistIndex = player.getPlaylistIndex();
        //we only want to fetch a new video from the server whenever we have reached the END of 
        //the current playlist. This allows us to navigate backward and forward
        //using the jwplayer playlist functionality without messing up future progress.
        if (playlistIndex == playlist.length - 1) {
            getNextVideo(function(video) {
                if (video == undefined) {
                    //exit fullscreen mode.
                    player.setFullscreen(false);
                    showPlaybackFinished();
                } else {
                    currentVideo = video;
                    playVideo(video);
                }
            });
        } else {
            var playlistItem = playlist[playlistIndex + 1];
            playVideo(playlistItem.video);
        }
    }

    function playVideo(video) {
        //create a new playlist item
        var playlistItem = {
            file: video.url,
            image: video.hdPosterUrl,
            title: video.title,
            video: video
        };
        var playlist = player.getPlaylist();
        //add this video to the playlist
        playlist.push(playlistItem);
        // displayVideoTitle(video);
        //re-add the playlist to the jwplayer, and set the last video in the playlist as the next video
        player.load(playlist);

        //tell the player to play the item we JUST ADDED
        player.playlistItem(playlist.length - 1);
    }

    function showPlaybackFinished() {
        // $("#playbackFinished").show();
    }

    function displayVideoTitle(video) {
        var baseUrl = app.baseUrl;
        //display the title of the video
        if (video.mediaType == enumerations.tvEpisode) {
            var title =
                    "<a href='" + baseUrl + "/Home/VideoInfo?videoId=" + video.videoId + "'>" +
                    video.showName + "</a>"
                    + " Season " + video.seasonNumber +
                    " Episode " + video.episodeNumber + " - " + video.title;
            $("#playTitle").html(title);
        } else {
            var title = "<a href='" + baseUrl + "/Home/VideoInfo?videoId=" + video.videoId + "'>" +
                    video.title +
                    "</a>";
            $("#playTitle").html(title);
        }
    }

    function keyboardShortcuts(e) {
        switch (e.which) {
            //spacebar
            case 32:
                //toggle playback
                jwplayer().play();
                break;
        }
    }

    function resizePlayer() {
        var $bodyRow = $("#bodyRow");
        var width = $bodyRow.width();
        var height = app.bodyRowHeight() - 10;
        player.resize(width, height);
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
    /**
     * Event that is fired every time the video starts playing
     */
    function onPlay() {
        //resize the player to fill the window
        resizePlayer();
        playPositionUpdateTime = new Date();
    }

    /**
     * Event that is called every time the video changes time position. This may be called up to 
     * 10 times a second
     */
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
            updateVideoPosition(getCurrentVideo().videoId, positionInSeconds);
        }
    }

    /**
     * Updates the time position of the video in the database
     * @param {type} videoId
     * @param {type} seconds
     * @param {type} bFinished
     * @returns {undefined}
     */
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

    function updatePlaylistItemFinished(playlistName, playlistItemId) {
        bFinished = bFinished !== undefined ? bFinished : false;
        $.ajax('api/PlaylistItemFinished.php', {
            data: {
                playlistName: playlistName,
                playlistItemId: playlistItemId
            }
        }
        );
    }
});
