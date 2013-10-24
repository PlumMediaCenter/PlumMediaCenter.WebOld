$.widget("ui.playlistAdder", {
    version: "1",
    options: {
        username: ""
    },
    modal: null,
    modalBody: null,
    addToNewPlaylistButton: null,
    newPlaylistTxt: null,
    videoId: null,
    _playlistNames: [],
    _create: function() {
        var me = this;
        var s = '<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">'
                + '<div class="modal-dialog">'
                + '  <div class="modal-content">'
                + '  <div class="modal-header">'
                + '     <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>'
                + '     <h4 class="modal-title">Add to a playlist</h4>'
                + ' </div>'
                + '<div style="padding-left:10px;">Click an existing playlist or add a new one</div>'
                + '<div class="modal-body">'
                + '</div>'
                + ' <div class="modal-footer">'
                + '<input type="text" style="float:left;" id="playlistAdderNewPlaylistInput" placeholder="New Playlist Name"/>'
                + ' <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>'
                + '  <button id="addToNewPlaylistButton" type="button" class="btn btn-primary">Add To New Playlist</button>'
                + ' </div>'
                + ' </div>'
                + ' </div>'
                + ' </div>';
        this.modal = $(s);
        $('body').append(this.modal);
        this.modalBody = this.modal.find(".modal-body").first();
        this.addToNewPlaylistButton = this.modal.find("#addToNewPlaylistButton").first();
        this.newPlaylistTxt = this.modal.find("#playlistAdderNewPlaylistInput").first();
        this.addToNewPlaylistButton.click(function() {
            var newPlaylistName = me.newPlaylistTxt.val();
            if (newPlaylistName == "") {
                alert("Please enter a playlist name");
                return;
            }
            me.addToPlaylist.call(me, newPlaylistName, me.videoId);
        });
        this.modal.find(".close").first().click(function() {
            me.hide.call(me);
        });
    },
    show: function(videoId) {
        var me = this;
        this.videoId = videoId;
        this.getPlaylistNames(function(result) {
            me.modalBody.html("");
            $(result).each(function() {
                var name = this;
                var s = "<a>" + name + "</a>";
                var $s = $(s);
                $s.data("name", name);
                $s.click(function() {
                    me.addToPlaylist.call(me, $(this).data("name"), me.videoId);
                });
                me.modalBody.append($s).append("<br/>");
            });
            me.modal.modal('show');
        });
    },
    hide: function() {
        this.modal.modal('hide');
        $(".modal-backdrop").hide();
    },
    getPlaylistNames: function(callback) {
        var me = this;
        $.getJSON('api/GetPlaylistNames.php', {username: this.options.username}, function(result) {
            me._playlistNames = result;
            callback(result);
        });
    },
    addToPlaylist: function(playlistName, videoId) {
        var me = this;
        $.getJSON("api/AddToPlaylist.php", {username: this.options.username, playlistName: playlistName, videoIds: [videoId]}, function() {
            me.hide();
        });
    }

});