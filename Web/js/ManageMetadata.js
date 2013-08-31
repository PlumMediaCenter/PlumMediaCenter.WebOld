function action(action) {
    var $r = $(".warning");
    //if no row was selected, stop executing
    if ($r.length === 0) {
        return;
    }
    //draw a box on top of this row

    $.ajax(
            "ajax/MetadataManager.php", {
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
                    if (response.responseText != "false") {
                        var updatedNotification = $("<tr><td colspan='5' style='background-color:black;color: white; height: " + $r.height() + "px;'>Updated </td></tr>").insertAfter($r);

                        //$r.remove();

                        setTimeout(function() {
                            $(response.responseText).insertAfter(updatedNotification);
                            updatedNotification.remove();
                        }, 1000);
                    } else {
                        alert("Failed from json");
                    }
                }
    });

}

function setMediaType(type) {
    mediaType = type;
}