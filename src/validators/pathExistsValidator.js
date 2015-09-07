angular.module('app').directive('pathExistsValidator', function($http, $q) {
    return {
        require: 'ngModel',
        link: function(scope, element, attrs, ngModel) {
            ngModel.$asyncValidators.pathExists = function(modelValue, viewValue) {
                var value = modelValue || viewValue;
                return $http.get('api/GetPathExistsOnServer.php', {params: {path: value}}).then(
                        function(response) {
                            if (!response.data.exists === true) {
                                return $q.reject(response.data.errorMessage);
                            }
                            return true;
                        }
                );
            };
        }
    };
});