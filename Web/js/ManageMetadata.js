function fetchMetadata() {

}

function action(action) {
    var $r = $(".warning");
    $.getJSON("Ajax/MetadataManager.php",
            {
                baseUrl: $r.attr("baseurl"),
                basePath: $r.attr("basepath"),
                fullPath: $r.attr("fullpath"),
                mediaType: $r.attr("mediatype"),
                action: action
            },
    function(json) {
        if (json == true) {
            alert("Success");
            window.location.reload();
        } else {
            alert("Failed from json");
        }
    }
    ).fail(function() {
        alert("Failed")
    });
}