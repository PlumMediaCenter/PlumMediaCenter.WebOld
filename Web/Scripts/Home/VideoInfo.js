(function($, undefined) {
    $(document).ready(function() {

        //register events
        $(".episodeRow").click(function() {
            var $this = $(this);
            var sNum = parseInt($this.attr("data-season-number"));
            var eNum = parseInt($this.attr("data-episode-number"));
            showEpisodeInfo(sNum, eNum);
        });
        $("img").error(function() {
            $(this).css({visibility: "hidden"});
        });
        $(document).keydown(keydown);

        //'click' the current episode row, if one exists
        $("tr.nextEpisodeRow").click();
        //scroll the window back to the top, since displaying 

    });

    function keydown(e) {
        switch (e.keyCode) {
            case 40://down
                //find the currently highlighted row
                var r = $(".selected");
                var newRow = r.length > 0 ? r.next(".episodeRow") : $(".episodeRow").first();
                //if no items were found, we are at the bottom of the list
                if (newRow.length == 0) {
                    return;
                }
                r.removeClass("selected");
                newRow.addClass("selected");
                showEpisodeInfo.call(newRow[0], e);
                e.preventDefault();
                break;
            case 38: //up
                //find the currently highlighted row
                var r = $(".selected");
                var newRow = r.length > 0 ? r.prev(".episodeRow") : $(".episodeRow").first();
                //if no items were found, we are at the top of the list
                if (newRow.length == 0) {
                    return;
                }
                r.removeClass("selected");
                newRow.addClass("selected");
                showEpisodeInfo.call(newRow[0], e);
                e.preventDefault();
                break;
            case 13://enter   
                //play the selected video
                var r = $(".selected");
                if (r.length > 0) {
                    playVideo.call(r[0]);
                }
                e.preventDefault();

                break;
        }
    }

    function playVideo() {
        var row = $(this);
        window.location = row.find(".play").attr('href');
    }

    function showEpisodeInfo(seasonNumber, episodeNumber) {
        //find the row with the specified season and episode number
        var $row = $("#videoInfoEpisodeTable tr").filter(function() {
            var $this = $(this);
            var sNum = parseInt($this.attr("data-season-number"));
            var eNum = parseInt($this.attr("data-episode-number"));
            return sNum === seasonNumber && eNum === episodeNumber
        });

        //if no episode row was found, tell the user what happened.
        if ($row.length === 0) {
            alert("Cannot find episode s" + seasonNumber + " e" + episodeNumber);
        }

        var videoId = parseInt($row.attr("data-video-id"));
        var v = getVideo(videoId);
        var year = (v.year !== undefined) ? new Date(parseInt(v.year)).getFullYear() : "";
        var html = "" +
                "    <img id='episodePoster' src='" + v.hdPosterUrl + "'/>" +
                "   <p>Season <span id='seasonNumber'>" + v.seasonNumber + "</span> Episode <span id='episodeNumber'>" + v.episodeNumber + "</span>" +
                "       <br/><span id='mpaa'>" + v.mpaa + "</span>" +
                "       <br/><span id='year'>" + year + "</span>" +
                "    </p>" +
                "    <div id='plot'>" + v.plot + "</div>";

        //unhighlight all rows
        $(".episodeRow").removeClass("selected");
        //highlight the selected row so we know which row is selected
        $row.addClass("selected");
        $("#episodeInfo").removeClass("hide").html(html);

        //scroll to the episode info after a timeout so that the browser has time to 
        //render the height of the episode info first
        setTimeout(function() {
            var offset = $("#episodeInfo").offset();
            $("body").scrollTop(offset.top);
        }, 100);

//        $("body").animate({
//            scrollTop: offset.top,
//            scrollLeft: offset.left
//        });
        //scroll the episode info into view
    }

    function getVideo(videoId) {
        for (var i in video.episodes) {
            var episode = video.episodes[i];
            if (episode.videoId === videoId) {
                return episode;
            }
        }
    }
})(jQuery, undefined);