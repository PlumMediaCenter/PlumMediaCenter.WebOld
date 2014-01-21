(function($, undefined) {
    $.widget("plum.video", {
        version: "1",
        options: {
            video: undefined
        },
        _create: function() {
            var v = this.options.video;
            this.element.addClass("tile");
            var videoMarkup = "<span>" +
                    " <a title = 'Play Movie' href = 'Play.php?videoId=" + v.videoId + "' > " + v.title + "</a> " +
                    " </span> " +
                    " <a href = 'VideoInfo.php?videoId=" + v.videoId + "' > " +
                    " <img class='tileImg' src = '" + v.hdPosterUrl + "' > </a> " +
                    " ";
            this.element.html(videoMarkup);
        }
    });
})(jQuery, undefined);