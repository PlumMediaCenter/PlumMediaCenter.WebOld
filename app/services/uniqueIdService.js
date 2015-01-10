angular.module('app').service('uniqueId', [function() {
        var counter = 0;
        return function() {
            counter++;
            return 'element-' + counter;
        }
    }]);