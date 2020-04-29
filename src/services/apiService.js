angular.module('app').service('api', ['$q', '$http', function ($q, $http) {
    return {
        generateLibrary: function () {
            return $q(function (resolve, reject) {
                $http.get('api/GenerateLibrary.php').success(function (result) {
                    if (result.success) {
                        resolve();
                    } else {
                        reject();
                    }
                }).error(function (e) {
                    reject(e);
                })
            });
        }
    };
}]);