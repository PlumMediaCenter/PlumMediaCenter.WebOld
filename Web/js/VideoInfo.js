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
});

function showEpisodeInfo(e) {
    //$("#episodeInfo").hide();
    $row = $(this);
    setTimeout(function() {
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
    }, 50);
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