angular.module('app').service('api', ['$q', '$http', function($q, $http) {
        return {
            generateLibrary: generateLibrary
        };

        function generateLibrary() {
            return $q(function(resolve, reject) {
                $http.get('api/GenerateLibrary.php')
                        .success(function(result) {
                            if (result.success) {
                                resolve();
                            } else {
                                reject();
                            }
                        })
                        .error(function() {
                            reject();
                        })

            });
        }
    }]);