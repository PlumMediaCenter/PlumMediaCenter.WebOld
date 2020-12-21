angular.module('app').service('api', ['$q', '$http', function ($q, $http) {
    return {
        generateLibrary: function () {
            return $http.get('api/GenerateLibrary.php').then(function (response) {
                return response.data;
            });
        }
    };
}]);