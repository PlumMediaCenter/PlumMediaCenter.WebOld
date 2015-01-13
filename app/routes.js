angular.module('app')
        .config(['$stateProvider', '$urlRouterProvider',
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
                            url: '/videoInfo/{videoId:int}',
                            templateUrl: 'app/partials/videoInfo.html',
                            controller: 'VideoInfoController',
                            controllerAs: 'vm'
                        })
                        .state('videoSources', {
                            url: '/videoSources',
                            templateUrl: 'app/partials/videoSources.html',
                            controller: 'VideoSourcesController',
                            controllerAs: 'vm'
                        })
                        .state('editVideoSource', {
                            url: '/editVideoSource/{id:int}',
                            parent: 'videoSources',
                            templateUrl: 'app/partials/editVideoSource.html',
                            controller: 'EditVideoSourceController',
                            controllerAs: 'vm'
                        })
                        .state('play', {
                            url: '/play/{videoId:int}',
                            templateUrl: 'app/partials/play.html',
                            controller: 'PlayController',
                            controllerAs: 'vm'
                        })
                        .state('search', {
                            url: '/search?q',
                            templateUrl: 'app/partials/search.html',
                            controller: 'SearchController',
                            controllerAs: 'vm'
                        })
            }])

        .run(['$rootScope', '$state', '$stateParams', function($rootScope, $state, $stateParams) {
                $rootScope.$state = $state;
                $rootScope.$stateParams = $stateParams;
            }])