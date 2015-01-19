angular.module('app').controller('BaseController', ['globals',function(globals) {
        var vm = angular.extend(this, {
            globals: globals
        });
        
    }]);