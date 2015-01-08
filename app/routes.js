angular.module('app').config(['$stateProvider', '$urlRouterProvider',
    function($stateProvider, $urlRouterProvider) {
        $urlRouterProvider.otherwise('/home');

        $stateProvider
                .state('home', {
                    url: '/home',
                    templateUrl: 'app/partials/home.html',
                    controller: 'HomeController',
                    controllerAs: 'vm'
                })
                 .state('admin', {
                    url: '/admin',
                    templateUrl: 'app/partials/admin.html',
                    controller: 'AdminController',
                    controllerAs: 'vm'
                })
                  .state('videoInfo', {
                    url: '/videoInfo/:videoId',
                    templateUrl: 'app/partials/videoInfo.html',
                    controller: 'VideoInfoController',
                    controllerAs: 'vm'
                })
    }]);