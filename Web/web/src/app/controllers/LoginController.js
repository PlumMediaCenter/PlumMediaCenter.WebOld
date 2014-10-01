angular.module('app').controller('LoginController', ['$scope', 'account', function ($scope, account) {
    'use strict';
    var vm = $scope;
    vm.email = 'abc';
    vm.password = 'a';

    vm.logIn = function () {
        account.logIn(vm.email, vm.password).then(function () {

        }, function () {

        });
    };
    return vm;
}
]);