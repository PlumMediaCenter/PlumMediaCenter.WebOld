angular.module('app').directive('focus', ['$timeout', '$parse', function($timeout, $parse) {
        return {
            restrict: 'A',
            link: function($scope, element, attributes, controller) {
                $scope.$watch(function() {
                    return $scope.$eval(attributes.focus);
                }, function(focus) {
                    if (focus === true) {
                        $timeout(function() {
                            element[0].focus();
                            getter = $parse(attributes.focus);
                            //override the value with a false now that we have performed the focus
                            getter.assign($scope, false);
                        });
                    }
                });
            }
        }

    }]);