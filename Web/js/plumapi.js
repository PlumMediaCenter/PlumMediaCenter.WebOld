var plumapi = {};
plumapi.getGenreList = function(callback) {
    //if callback is not a function
    if (typeof callback !== "function") {
        callback = function() {
        };
    }
    $.getJSON("api/GetGenreList.php", {
    }).done(function(json, status) {
        if (status === "success") {
            callback(json);
        } else {
            callback(false);
        }
    });
}

/**
 * Gets a list of videos in the specified genre.
 * @param string genreName - the name of the genre to fetch videos for
 * @param function callback - the function to call once this api call has finished
 */
plumapi.getGenreVideos = function(genreName, callback) {
    $.ajax("api/GetGenreVideos.php", {
        dataType: "json",
        data: {genreName: genreName},
        complete: function(result, status) {
            plumapi._callback(callback)(result.responseJSON);
        }
    });
};


/**
 * Checks the provided callback to see if it is actually a function. if it is NOT a function, 
 * a generic empty function is returned instead. This allows the api to fail gracefully
 * without having to duplicate the callback check in every function
 * @param function callback
 * @returns function
 */
plumapi._callback = function(callback) {
    //if callback is not a function
    if (typeof callback !== "function") {
        return function() {
        };
    } else {
        return callback;
    }
}