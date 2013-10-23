$.widget("ui.playlist", {
    version: "1",
    options: {
        playlistName: ""
    },
    list: null,
    _playlist: [],
    _create: function() {
        //create an unordered list and append it directly inside of the matched element
        this.list = $("<ul class='playlistGrid'></ul>");
        $(this.element).append(this.list)
        this._fetchPlaylist();
    },
    clear: function() {
        $(this.list).html("");
    },
    _fetchPlaylist: function() {
        var me = this;
        $.getJSON("api/GetPlaylist.php", {playlistName: this.options.playlistName}, function(playlistObj) {
            me._playlist = playlistObj;
            //clear the playlist
            me.clear();
            for (var i in me._playlist) {
                var video = me._playlist[i];
                var li = "<li>";
                //the delete button
                li += "<a style='cursor:pointer;' href='VideoInfo.php?videoId=" + video.videoId + "'>";
                li += "<img style='top: 50%;' src='" + video.hdPosterUrl + "'/>";
                li += "</a>";
                li += "<div class='title' title='" + video.title + "'>" + video.title + "</div>";
                li += "</li>";
                var $li = $(li);
                $li.data("video", video);
                me.list.append($li);
                $closeBtn = $("<span class='closeBtn red ui-icon ui-icon-close'></span>");
                $closeBtn.click(function(e) {
                    var listItem = $(this).closest("li")
                    me.removeFromPlaylist.call(me, listItem, e);
                });
                $li.prepend($closeBtn);
            }
            me.list.sortable({stop: function() {
                    me.savePlaylist.call(me);
                }
            });
            me.list.disableSelection();
        });
    },
    removeFromPlaylist: function(listItem, event) {
        var me = this;
        //remove the element from the list.
        $(listItem).fadeOut(300, function() {
            //after the element finishes animating, remove it.
            $(this).remove();
            //update the database
            me.savePlaylist();
        });

    },
    /**
     * Get the list of videoIds in order
     */
    getVideoIds: function() {
        var videoIds = [];
        $(this.list).find("li").each(function() {
            var video = $(this).data("video");
            videoIds.push(video.videoId);
        });
        return videoIds;
    },
    savePlaylist: function() {
        var videoIds = this.getVideoIds();
        $.getJSON("api/SetPlaylist.php", {playlistName: this.options.playlistName, videoIds: videoIds}, function(result) {
            if (result.success === false) {

            }
        });
    }
});