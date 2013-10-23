$(document).ready(function() {
    $("#playlistDropdownLink").click(getPlaylistNames);
});

var playlists = [];
function getPlaylistNames() {
    if (playlists.length != 0) {
        return;
    }
    $.getJSON("api/GetPlaylists.php", {}, function(dataPlaylists) {
        playlists = dataPlaylists;
        $("#playlist").html("");
        for (var name in dataPlaylists) {
            $a = $(" <li><a style='cursor:pointer;' onclick=\"showPlaylist('" + name + "');return false;\">" + name + "</a></li>");
            $("#playlist").append($a);
        }
    });
}

function showPlaylist(playlistName) {
    var playlist = playlists[playlistName];
    var $back = $("<li><a style='cursor:pointer;'>Back to Playlists</a></li>").click(function() {
        //clear the playlists 
        playlists = [];
    });
    $("#playlist").html($back);
    for (var i in playlist) {
        var video = playlist[i];
        var $a = $(" <li><a href=>" + name + "<br/><img src='" + video.hdPosterUrl + "'/></a></li>");
        $("#playlist").append($a);
    }

}