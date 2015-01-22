angular.module('app').service('Video', ['$http', '$q', '_', function($http, $q, _) {
        function Video() {

        }

        Video.search = function(searchTerm) {
            var deferred = $q.defer();
            $http.get('api/GetSearchResults.php', {params: {title: searchTerm}}).success(function(data) {
                deferred.resolve(data);
            }).error(function() {
                deferred.reject();
            });
            return deferred.promise;
        };

        Video.getAll = function() {
            return $q(function(resolve, reject) {
                $http.get('api/GetLibrary.php').success(function(data) {
                    resolve(data);
                });
            });
        };

        Video.getById = function(id) {
            var deferred = $q.defer();
            if (!_.isNumber(id)) {
                deferred.reject();
            } else {
                $http.get('api/GetVideo.php?videoId=' + id).success(function(data) {
                    deferred.resolve(data);
                }).error(function() {
                    deferred.reject(data);
                });
            }
            return deferred.promise;
        };

        Video.getEpisodes = function(showId) {
            var deferred = $q.defer();
            $http.get('api/GetTvEpisodes.php?videoId=' + showId).success(function(data) {
                deferred.resolve(data);
            }).error(function() {
                deferred.reject();
            });
            return deferred.promise;
        };

        Video.getNextEpisode = function(showId) {
            var deferred = $q.defer();
            $http.get('api/GetNextEpisode.php?videoId=' + showId).success(function(data) {
                deferred.resolve(data);
            }).error(function() {
                deferred.reject();
            });
            return deferred.promise;
        };

        /**
         * Get how much percentage watched this video is
         * @param {type} videoId
         * @returns {$q@call;defer.promise}
         */
        Video.getProgressPercent = function(videoId) {
            var deferred = $q.defer();
            $http.get('api/GetVideoProgressPercent.php', {params: {videoId: videoId}})
                    .success(function(result) {
                        deferred.resolve(result.percent);
                    })
                    .error(deferred.reject);
            return deferred.promise;

        };

        /**
         * Get the number of seconds into a video the current user is. 
         * @param {type} videoId
         * @returns {$q@call;defer.promise}
         */
        Video.getProgress = function(videoId) {
            var deferred = $q.defer();
            $http.get('api/GetVideoProgress.php', {params: {
                    videoId: videoId
                }}).success(function(data) {
                deferred.resolve(data.startSeconds);
            }).error(function() {
                deferred.reject();
            });
            return deferred.promise;
        }

        Video.setProgress = function(videoId, seconds, isFinished) {
            isFinished = isFinished === true ? true : false;

            var deferred = $q.defer();
            $http.get('api/SetVideoProgress.php', {params: {
                    videoId: videoId,
                    seconds: seconds,
                    finished: isFinished
                }}).success(function(data) {
                if (data.success) {
                    deferred.resolve();
                } else {
                    deferred.reject();
                }
            }).error(function() {
                deferred.reject();
            });
            return deferred.promise;
        }

        Video.getCounts = function() {
            var deferred = $q.defer();
            $http.get('api/GetVideoCounts.php').success(function(data) {
                deferred.resolve(data);
            }).error(function() {
                deferred.reject();
            });
            return deferred.promise;
        };

        Video.fetchMetadata = function(videoId, onlineVideoId) {
            var deferred = $q.defer();
            $http.get('api/FetchVideoMetadata.php', {params: {videoId: videoId, onlineVideoId: onlineVideoId}}).success(function(data) {
                deferred.resolve(data);
            }).error(function() {
                deferred.reject();
            });
            return deferred.promise;
        };

        Video.getMetadataSearchResultsByTitle = function(mediaType, title) {
            var deferred = $q.defer();
            $http.get('api/GetMetadataSearchResults.php', {
                params: {
                    mediaType: mediaType,
                    title: videoId
                }
            }).success(function(data) {
                deferred.resolve(data);
            }).error(function() {
                deferred.reject();
            });
            return deferred.promise;
        }
             Video.getMetadataSearchResultsByOnlineVideoId = function(mediaType, onlineVideoId) {
            var deferred = $q.defer();
            $http.get('api/GetMetadataSearchResults.php', {
                params: {
                    mediaType: mediaType,
                    onlineVideoId: onlineVideoId
                }
            }).success(function(data) {
                deferred.resolve(data);
            }).error(function() {
                deferred.reject();
            });
            return deferred.promise;
        }
        

        return Video;
    }]);