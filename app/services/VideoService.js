angular.module('app').service('Video', ['$http', '$q', '_', function($http, $q, _) {
        function Video() {

        }

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
                deferred.reject(data);
            });
            return deferred.promise;
        };

        Video.getNextEpisode = function(showId) {
            var deferred = $q.defer();
            $http.get('api/GetNextEpisode.php?videoId=' + showId).success(function(data) {
                deferred.resolve(data);
            }).error(function() {
                deferred.reject(data);
            });
            return deferred.promise;
        };

        Video.setProgress = function(videoId, seconds, isFinished) {
            isFinished = isFinished === true ? true : false;

            var deferred = $q.defer();
            $http.get('api/PostVideoProgress.php', {params: {
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
                deferred.reject(data);
            });
            return deferred.promise;
        }
        return Video;
    }]);