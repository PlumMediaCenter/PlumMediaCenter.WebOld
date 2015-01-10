angular.module('app').service('Video', ['$http', '$q', function($http, $q) {
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
            $http.get('api/GetVideo.php?videoId=' + id).success(function(data) {
                deferred.resolve(data);
            }).error(function() {
                deferred.reject(data);
            });
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
        
        
        return Video;
    }]);