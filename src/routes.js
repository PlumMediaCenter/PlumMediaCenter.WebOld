angular.module('app')
        .config(['$stateProvider', '$urlRouterProvider',
            function($stateProvider, $urlRouterProvider) {
                $urlRouterProvider.otherwise('/home');

                $stateProvider
                   .state('addNewMediaItem', {
                            url: '/addNewMediaItem',
                            templateUrl: '/addNewMediaItem.html',
                            controller: 'AddNewMediaItemController',
                            controllerAs: 'vm'
                        }) 
                        .state('home', {
                            url: '/home',
                            templateUrl: '/home.html',
                            controller: 'HomeController',
                            controllerAs: 'vm'
                        })
                        .state('admin', {
                            url: '/admin',
                            templateUrl: '/admin.html',
                            controller: '/AdminController',
                            controllerAs: 'vm'
                        })
                        .state('videoInfo', {
                            url: '/videoInfo/{videoId:int}',
                            templateUrl: '/videoInfo.html',
                            controller: 'VideoInfoController',
                            controllerAs: 'vm'
                        })
                        .state('videoSources', {
                            url: '/videoSources',
                            templateUrl: '/videoSources.html',
                            controller: 'VideoSourcesController',
                            controllerAs: 'vm'
                        })
                        .state('editVideoSource', {
                            url: '/editVideoSource/{id:int}',
                            parent: 'videoSources',
                            templateUrl: '/editVideoSource.html',
                            controller: 'EditVideoSourceController',
                            controllerAs: 'vm'
                        })
                        .state('play', {
                            url: '/play/{videoId:int}?{showVideoId:int}',
                            templateUrl: '/play.html',
                            controller: 'PlayController',
                            controllerAs: 'vm'
                        })
                        .state('search', {
                            url: '/search?q',
                            templateUrl: '/search.html',
                            controller: 'SearchController',
                            controllerAs: 'vm'
                        })
                        .state('metadataFetcher', {
                            url: '/metadataFetcher/{videoId:int}',
                            templateUrl: '/metadataFetcher.html',
                            controller: 'MetadataFetcherController',
                            controllerAs: 'vm'
                        }) 
            }])

        .run(['$rootScope', '$state', '$stateParams', function($rootScope, $state, $stateParams) {
                $rootScope.$state = $state;
                $rootScope.$stateParams = $stateParams;
            }])