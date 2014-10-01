angular.module('app').config(['$routeProvider', '$locationProvider', function ($routeProvider, $locationProvider) {
    'use strict';
    $locationProvider.html5Mode(true);
    $routeProvider
        .when('/login', {
            templateUrl: '/partials/login.html'
        })
        .when('/register', { templateUrl: '/partials/register.html' })
        .otherwise({ 
            redirectTo: '/login'
        })
    ;
}]);