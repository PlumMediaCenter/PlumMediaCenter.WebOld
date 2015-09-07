angular.module('app').service('admin', ['$http', '$q', '_', function($http, $q, _) {
        return {
            getServerVersionNumber: getServerVersionNumber,
            updateApplication: updateApplication
        };

        /**
         * Finds a poster and metadata for every video in the library that does not have one yet.
         * @returns {$q@call;defer.promise}
         */
        function getServerVersionNumber() {
            var deferred = $q.defer();
            $http.get('api/GetServerVersionNumber.php')
                    .success(function(result) {
                        deferred.resolve(result);
                    })
                    .error(function() {
                        deferred.reject();
                    });
            return deferred.promise;
        }

        /**
         * Checks for updates to this application and updates if there are any.
         * @returns {undefined}
         */
        function updateApplication() {
            var deferred = $q.defer();
            $http.get('api/Update.php')
                    .success(function(result) {
                        if (result.success === true) {
                            deferred.resolve(result);
                        } else {
                            deferred.reject(result);
                        }
                    })
                    .error(function(err) {
                        deferred.reject(err);
                    });
            return deferred.promise;
        }
    }]);