(function($, undefined) {
    var defaults = {
        baseUrl: undefined
    };
    window.plumapi = (window.plumapi === undefined) ? defaults : $.extend(defaults, window.plumapi);

    plumapi.deleteVideoSource = function(sourcePath, callback) {
        plumapi._call("DeleteVideoSource", {sourcePath: sourcePath}, callback);
    };

    plumapi.FetchMissingMetadata = function(callback) {
        plumapi._call("FetchMissingMetadata", {}, callback);
    };

    /**
     * Re-generates the library. It will scan all source folders and 
     * add any videos it finds to the library, as well as remove any videos no longer found
     * in the watch folder
     * @param {funtion} callback - a function to be called after the api call has completed
     * @returns boolean - true if successful, false if failure or error
     */
    plumapi.generateLibrary = function(callback) {
        plumapi._call("GenerateLibrary", {}, callback);
    };

    plumapi.getLibrary = function(callback) {
        plumapi._call("GetLibrary", {}, callback);
    };

    plumapi.getNextEpisode = function(videoId, callback) {
        plumapi._call("GetNextEpisode", {videoId: videoId}, callback);
    };


    plumapi.getTvShow = function(videoId, callback) {
        plumapi._call("GetTvShow", {videoId: videoId}, callback);
    };


    plumapi.getVideoProgress = function(videoId, callback) {
        plumapi._call("GetVideoProgress", {videoId: videoId}, callback);
    };

    plumapi.serverExists = function(callback) {
        plumapi._call("ServerExists", {}, callback);
    };

    plumapi.setVideoProgress = function(videoId, seconds, bFinished, callback) {
        plumapi._call("SetVideoProgress", {
            videoId: videoId,
            seconds: seconds,
            finished: bFinished
        }, callback);

    };

    plumapi.getGenreList = function(callback) {
        plumapi._call("GetGenreList", {}, callback);
    };

    /**
     * Gets a list of videos in the specified genre.
     * @param string genreName - the name of the genre to fetch videos for
     * @param function callback - the function to call once this api call has finished
     */
    plumapi.getGenreVideos = function(genreName, callback) {
        plumapi._call("GetGenreVideos", {genreName: genreName}, callback);
    };


    plumapi.addToPlaylist = function(playlistName, videoIds, callback) {
        plumapi._call("AddToPlaylist", {
            playlistName: playlistName,
            videoIds: videoIds
        },
        callback);
    };

    plumapi._call = function(name, data, callback) {
        $.ajax(plumapi.baseUrl + "API/" + name, {
            dataType: "json",
            data: data,
            complete: function(result, status) {
                //return false if failure or the result if success
                var returnVal = (status === "success") ? result.responseJSON : false;
                plumapi._callback(callback)(returnVal);
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
    };
})(jQuery, undefined);