(function($, undefined) {
    $.widget("plum.video", {
        version: "1",
        options: {
            video: undefined
        },
        _create: function() {
            var v = this.options.video;
            this.element.addClass("video-tile");
            var videoMarkup = 
                    " <a href = 'VideoInfo.php?videoId=" + v.videoId + "' > " +
                    " <img class='poster' src = '" + v.hdPosterUrl + "' > </a> " +
                    " ";
            this.element.html(videoMarkup);
        }
    });
})(jQuery, undefined);