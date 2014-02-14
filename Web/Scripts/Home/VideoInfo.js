/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function() {
    $(".episodeRow").hover(showEpisodeInfo, hideEpisodeInfo);
    $("img").error(function() {
        $(this).css({visibility: "hidden"});
    });

    $(document).keydown(keydown);
});

function keydown(e) {
    switch (e.keyCode) {
        //down
        case 40:
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
            break;
            //up
        case 38:
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
            break;
            //enter    
        case 13:
            //play the selected video
            var r = $(".selected");
            if (r.length > 0) {
                playVideo.call(r[0]);
            }
            break;
    }
}

function playVideo() {
    var row = $(this);
    window.location = row.find(".play").attr('href');
}

function showEpisodeInfo(e) {
    //$("#episodeInfo").hide();
    $row = $(this);
    // setTimeout(function() {
    var videoId = $row.attr("episodeId");
    var v = getVideo(videoId);
    // var top = e.pageY - 200;
    //var left = e.pageX + 20;
    $("#episodePoster").attr('src', v.hdPosterUrl).css({visibility: "visible"});
    $("#title").html(v.title);
    $("#plot").html(v.plot);
    $("#seasonNumber").html(v.seasonNumber);
    $("#episodeNumber").html(v.episodeNumber);
    $("#mpaa").html(v.mpaa);
    $("#mpaa").html(v.mpaa);

    var d = new Date(parseInt(v.year));
    $("#year").html(d.getFullYear());
    $("#episodeInfo").show();
    // $("#episodeInfo").offset({top: top, left: left});

    //highlight the selected row so we know which row is selected
    $(".episodeRow").removeClass("selected");
    $row.addClass("selected");
    // }, 50);
}

function hideEpisodeInfo() {
    //$("#episodeInfo").hide();
}

function getVideo(videoId) {
    for (var i in video.episodes) {
        var episode = video.episodes[i];
        if (episode.videoId == videoId) {
            return episode;
        }
    }
}