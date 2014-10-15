angular.module('app').controller('RootController', ['$scope','$rootScope', '$location', 
    function ($scope, $rootScope, $location) {
    'use strict';
    var vm = this;
    $rootScope.$on('$routeChangeStart', function (event, next, current) {
        vm.currentRoute = next !== undefined && next.$$route !== undefined && next.$$route.originalPath;
    });

    $scope.$watchCollection(function () { 
        return account.user
    }, function (newValue, oldValue) { 
        vm.user = newValue;
    });
}]); 