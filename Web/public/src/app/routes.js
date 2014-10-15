angular.module('app').config(['$routeProvider', '$locationProvider', function(
	$routeProvider, $locationProvider) {
	$locationProvider.html5Mode(true);
	$routeProvider
		.when('/login', {
			templateUrl: '/app/partials/login.html',
			controller: 'LoginController',
			controllerAs: 'vm'
		}).when('/index', {
			templateUrl: '/app/partials/index.html',
			controller: 'IndexController',
			controllerAs: 'vm'
		}).when('/page1', {
			templateUrl: '/app/partials/page1.html',
			controller: 'Page1Controller',
			controllerAs: 'vm'
		}).when('/page2', {
			templateUrl: '/app/partials/page2.html',
			controller: 'Page2Controller',
			controllerAs: 'vm'
		}).when('/account', {
			templateUrl: '/app/partials/account.html',
			controller: 'AccountController',
			controllerAs: 'vm'
		}).when('/videoSources', {
			templateUrl: '/app/partials/videoSources.html',
			controller: 'VideoSourcesController',
			controllerAs: 'vm'
		}).when('/addVideoSource', {
			templateUrl: '/app/partials/addVideoSource.html',
			controller: 'AddVideoSourceController',
			controllerAs: 'vm'
		}).otherwise({
			redirectTo: '/index'
		});
}]).run(['$rootScope', '$location', 'account', '$log', function($rootScope,
	$location, account, $log) {
	$rootScope.$on('$routeChangeStart', function(event, next, current) {
		//if the user is not currently logged in, redirect them to the login screen
		if (!account.isLoggedIn()) {
			$log.debug('User is not logged in. Redirecting to the login screen');

			//if the user is not already headed to the login screen, send them there
			if (next.$$route.templateUrl !== '/partials/login.html') {
				var search = {
					url: $location.url()
				};
				$location.path('/login').search(search);
			}
		}
	});
}]);