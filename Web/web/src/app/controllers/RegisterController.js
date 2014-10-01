angular.module('app').controller('RegisterController', ['$scope', 'account', 'api', function ($scope, account, api) {
    'use strict';
    var vm = this;
    vm.user = {
        firstName: 'Bronley1',
        lastName: 'Plumb',
        email: 'bronley@gmail.com',
        password: 'password',
        passwordConfirm: 'password',
        birthDate: new Date()
    };

    vm.register = function () {
       // api.users.save(vm.user, function () {
       // });
    };
    return vm;
}
]);