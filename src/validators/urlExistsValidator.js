angular.module('app').directive('urlExistsValidator', function($http, $q) {
    return {
        require: 'ngModel',
        link: function(scope, element, attrs, ngModel) {
            ngModel.$asyncValidators.urlExists = function(modelValue, viewValue) {
                var value = modelValue || viewValue;
                return $http.get('api/GetUrlExists.php', {params: {url: value}}).then(
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