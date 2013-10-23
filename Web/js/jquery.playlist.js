$.widget("ui.playlist", {
    version: "1",
    options: {
        playlistName: ""
    },
    _create: function() {
        //create an unordered list and append it directly inside of the matched element
        this.list = $("<ul class='playlistGrid'></ul>");
        $(this.element).append(this.list)
        this._fetchPlaylist(this.options.playlistName);
    },
    list: null,
    _playlist: [],
    clear: function() {
        $(this.list).html("");
    },
    _fetchPlaylist: function(playlistName) {
        var me = this;
        var playlistName = playlistName != undefined ? playlistName : this.options.playlistName;
        $.getJSON("api/GetPlaylist.php", {playlistName: playlistName}, function(playlistObj) {
            me._playlist = playlistObj;
            //clear the playlist
            me.clear();
            for (var i in me._playlist) {
                var video = me._playlist[i];

                var s = "<li>";
//                s += "<a style='cursor:pointer;' onclick=\"showPlaylist('" + name + "');return false;\">";
                s += "<img src='" + video.hdPosterUrl + "'/>";
//                s += "</a>";
                s += "</li>";
                $a = $(s);
                me.list.append($a);
            }
            me.list.sortable();
            me.list.disableSelection();
        });

    }
});