angular.module('app').controller('LoginController', ['$scope', 'account', function ($scope, account) {
    'use strict';
    var vm = $scope;
    vm.email = 'abc';
    vm.password = 'a';

    vm.logIn = function () {
        account.logIn(vm.email, vm.password).then(function () {
            vm.message = 'Logged in';
        }, function () {
            vm.message = 'Unable to log in'; 
        });
    };
    return vm;
}
]);