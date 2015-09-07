angular.module('app').service('util', ['_', function(_) {
        return {
            blankItemInPlace: blankItemInPlace
        };

        function blankItemInPlace(item) {
            if (_.isArray(item)) {
                while (item.length > 0) {
                    item.pop();
                }
            } else {
                for (var i in item) {
                    delete item[i];
                }
            }
        }
    }]);