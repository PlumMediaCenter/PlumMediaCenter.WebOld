angular.module('app').service('VideoSource', ['$http', '$q', function($http, $q) {
        function VideoSource() {

        }

        VideoSource.getAll = function() {
            var deferred = $q.defer();
            $http.get('api/GetVideoSources.php').success(function(data) {
                deferred.resolve(data);
            }).error(function() {
                deferred.reject();
            });
            return deferred.promise;
        };
        
          VideoSource.getById = function(id) {
            var deferred = $q.defer();
            $http.get('api/GetVideoSourceById.php?id=' + id).success(function(data) {
                deferred.resolve(data);
            }).error(function() {
                deferred.reject();
            });
            return deferred.promise;
        };

        return VideoSource;
    }]);