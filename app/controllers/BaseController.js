angular.module('app').controller('BaseController', ['globals',function(globals) {
        var vm = this;
        vm.globals = globals;
        
    }]);