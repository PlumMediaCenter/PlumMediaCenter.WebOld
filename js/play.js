//a reference to the jwplayer object created during page load
var player;
var previousPlaylistIndex = 0;
var currentPlaylistIndex = 0;
var seekBurstSeconds = 30;
var seekPosition = 0;

$(document).ready(function() {
    //display the video title on the page
    $(document).keydown(keyboardShortcuts);
    jwplayer("videoPlayer").setup({
        //file: "",
        // image: "",
        autostart: true,
        primary: "html5",
        playlist: jwPlaylist,
        startparam: "start",
        wmode: 'transparent',
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
                seekPosition = 0;
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
            onPlay: onPlay,
            onSeek: onSeek
        }

    });
    //set the reference to the player object so we don't have to look for it again.
    player = jwplayer("videoPlayer");

});

function onSeek(obj){
    if(obj.position !== seekPosition && obj.offset !== seekPosition){
        seekPosition = offset;
    }
}
