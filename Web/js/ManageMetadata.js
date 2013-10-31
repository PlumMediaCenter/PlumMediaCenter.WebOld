$(document).ready(function() {
    $(".table-sort").tablesorter();
    $(".table-sort thead tr th").hover(
            function() {
                $(this).prop("title", "sort");
            },
            function() {
            });


    $(document).on('click', "tr", function() {
        rowClick(this);
    });

    $("input[name='showRows']").click(function() {
        showRows(this.value);
    });

    //resize the video grids to fill vertical space
    $(window).resize(resize);
    resize();
});


function showRows(style) {
    switch (style) {
        case "all":
            $("tr").show();
            break;
        case "missing":
            //$("tr").filter(":hidden").show();
            $("tr").each(function() {
                $this = $(this);
                //if this row does not have a data-complete=false, hide it
                if ($this.attr("data-complete") == "true") {
                    $this.hide();
                }
            });
            break;
    }
}

function rowClick(row) {
    $("tr.warning").removeClass("warning");
    $(row).addClass("warning");
    //pulsate(this, "red", 5000);
}

function resize() {
    var height = $(window).height() - 150;
    $("#tablesArea").height(height + "px");
    var newHeight = $("#tablesArea").height() - 70;
    $(".tableScrollArea").height(newHeight + "px");
}

function action(action) {
    var $r = $(".warning");
    //if no row was selected, stop executing
    if ($r.length === 0) {
        return;
    }
    //draw a box on top of this row

    $.ajax(
            "api/MetadataManager.php", {
        dataType: "json",
        data:
                {
                    baseUrl: $r.attr("baseurl"),
                    basePath: $r.attr("basepath"),
                    fullPath: $r.attr("fullpath"),
                    mediaType: $r.attr("mediatype"),
                    action: action
                },
        complete:
                function(response) {
                    //remove any of the table highlighting classes so the animation can happen
                    $r.removeClass("warning").removeClass("success").removeClass("error");
                    var json = response.responseJSON === undefined ? {} : response.responseJSON;
                    if (json.success === true) {
                        var newRow = $(json.output);
                        //newRow.removeClass("warning").removeClass("success").removeClass("error");
                        $r.css("background-color", "#dff0d8");
                        pulsate($r, "#3333CC", "#dff0d8", function() {
                            newRow.insertAfter($r);
                            $r.remove();
                            //rowClick(newRow);
                        });
                    } else {

                        pulsate($r, "#ff0000", "#f2dede", function() {
                        });
                    }
                }
    });

}

function pulsate(selector, color1, color2, callback) {
    var $item = $(selector);
    $item.removeClass("success");
    $item.animate({backgroundColor: color1}, 300).animate({backgroundColor: color2}, 300).animate({backgroundColor: color1}, 300).animate({backgroundColor: color2}, 300).animate({backgroundColor: color1}, 300).animate({backgroundColor: color2}, 300, callback);
}

function setMediaType(type) {
    mediaType = type;
    //load any metadata tables that need loaded
    loadMetadataTables();
}

function loadMetadataTables() {

    //if the movies have not been loaded yet, load them
    if (mediaType == enumerations.movie && moviesLoaded == false) {
        //set a waiting message
        $("#moviesTableArea").html("Loading <img src='img/ajax-loader.gif'/>")
        $.getJSON("ajax/GetMetadataManagerTables.php", {mediaType: mediaType},
        function(result) {
            moviesLoaded = true;
            $("#moviesTableArea").html(result[enumerations.movie]);
        });
    } else if ((mediaType == enumerations.tvShow && tvShowsLoaded == false) || (mediaType == enumerations.tvEpisode && tvEpisodesLoaded == false)) {
        $("#tvShowsTableArea").html("Loading <img src='img/ajax-loader.gif'/>")
        $("#tvEpisodesTableArea").html("Loading <img src='img/ajax-loader.gif'/>")

        $.getJSON("ajax/GetMetadataManagerTables.php", {mediaType: mediaType},
        function(result) {

            moviesLoaded = true;
            $("#tvShowsTableArea").html(result[enumerations.tvShow]);
            $("#tvEpisodesTableArea").html(result[enumerations.tvEpisode]);

        });
    } else {
        //do nothing
    }
}