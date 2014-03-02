(function($, undefined) {
    $.widget("plum.genreVideoList", {
        version: "1",
        options: {
            genreName: null,
            max: -1,
            baseUrl: undefined
        },
        genreContainer: null,
        _create: function() {
            var me = this;
            this.element.addClass("categoryScroller");
            //add a header
            this.element.append("<h1>" + this.options.genreName + "</h1>");
            me.genreContainer = $("<div class='genreContainer'></div>");
            this.element.append(me.genreContainer);
            //fetch the videos in this genre
            plumapi.getGenreVideos(this.options.genreName, function(videoList) {
                var curr = 0;
                for (var idx in videoList) {
                    var video = videoList[idx];
                    //add this video to the list
                    me.addVideo(video);
                    curr++;
                    //stop adding videos after the first n
                    if (me.options.max != -1 && curr < me.options.max ) {
                        break;
                    }
                }
                me.genreContainer.append("<div class='clearfix'></div>");
            });
        },
        addVideo: function(video) {
            var $v = $("<div></div>")
            this.genreContainer.append($v);
            $v.video({video: video, baseUrl: this.options.baseUrl});
        }
    });
})(jQuery, undefined);