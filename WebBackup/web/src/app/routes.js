angular.module('app').config(['$routeProvider', '$locationProvider', function ($routeProvider, $locationProvider) {
    'use strict';
    $locationProvider.html5Mode(true);
    $routeProvider

    .when('/login', {
        templateUrl: '/partials/login.html'
    }).when('/browse', {
        templateUrl: '/partials/browse.html'
    }).when('/dashboard', {
        templateUrl: '/partials/dashboard.html'
    }).when('/register', {
        templateUrl: '/partials/register.html'
    }).otherwise({
        redirectTo: '/dashboard'
    })
;
}]).run(['$rootScope', '$location', 'account', '$log', function ($rootScope, $location, account, $log) {
    'use strict'; 
    $rootScope.$on('$routeChangeStart', function (event, next, current) {
        //if the user is not currently logged in, redirect them to the login screen
        if (!account.isLoggedIn()) {
            $log.debug('User is not logged in. Redirecting to the login screen');

            //if the user is not already headed to the login screen, send them there
            if (next.$$route.templateUrl !== '/partials/login.html') {
                var search = { url: $location.url() };
                $location.path('/login').search(search);
            }
        }
    });
}]);