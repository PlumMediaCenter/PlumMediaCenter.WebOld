(function() {
	'use strict';
	angular.module('app', ['ngResource', 'ngRoute', 'ngStorage']);
}());
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
(function() {
	'use strict';
	angular.module('app')
		.controller('AccountController', [Controller]);

	function Controller() {
		var vm = this;
	}
}());
(function() {
	'use strict';
	angular.module('app').controller('IndexController', [Controller]);

	function Controller() {

	}
}());
(function() {
	'use strict';
	angular.module('app').controller('LoginController', ['$scope', '$location',
		'account', Controller
	]);

	function Controller($scope, $location, account) {
		var vm = $scope;
		vm.email = 'username@domain.com';
		vm.password = 'password';
		vm.rememberMe = true;

		//clear the error message after any change
		$scope.$watchCollection(function() {
			return [vm.email, vm.password, vm.rememberMe];
		}, function() {
			vm.error = undefined;
		});

		vm.logIn = function() {
			vm.error = undefined;
			account.logIn(vm.email, vm.password, vm.rememberMe).then(function() {
				var urlDestination = $location.search().url;
				//if the user was trying to get somewhere else before we redirected them, send them there now.
				urlDestination = urlDestination === undefined ? '/dashboard' :
					urlDestination;
				$location.url(urlDestination);
			}, function() {
				vm.error = 'Invalid email or password';
			});
		};
		return vm;
	}
}());
(function() {
	'use strict';
	angular.module('app')
		.controller('NavbarController', ['account', Controller]);

	function Controller(account) {
		var vm = this;
		vm.user = account.user;
	}
}());
(function() {
	'use strict';
	angular.module('app')
		.controller('Page1Controller', [Controller]);

	function Controller() {
		var vm = this;
	}
}());
(function() {
	'use strict';
	angular.module('app')
		.controller('Page2Controller', [Controller]);

	function Controller() {
		var vm = this;
	}
}());
(function() {
	'use strict';
	angular.module('app')
		.controller('RootController', [Controller]);

	function Controller() {
		var vm = this;
	}
}());
angular.module('app')
	.directive('logo', [function() {
		'use strict';
		return {
			template: '<div class="inline-block logo"><span class="logo-img"></span>Your Application</div>'
		};
	}]);
angular.module('app').service('account', ['api', '$q', '$localStorage',
	function(api, $q, $localStorage) {
		'use strict';
		var token;
		var service = {
			/**
			 Attempts to get an authentication token from the server. If remember me is provided, the token is saved in local storage
			 and is used in future page loads. If false, the user is only logged in for this session
			 */
			logIn: function(email, password, rememberMe) {
				//log out whoever is currently logged in
				service.logOut();

				var deferred = $q.defer();
				api.users.token({
					username: email,
					password: password
				}, function(authToken, b, c) {
					if (rememberMe === true) {
						$localStorage.token = authToken;
					} else {
						token = authToken;
					}

					//retrieve the user data from the api so we have it for the remainder of the session
					service.user = api.users.get({
						userId: service.token().userId
					});
					deferred.resolve(true);
				}, function(a, b, c) {
					return deferred.reject(false);
				});
				return deferred.promise;
			},
			/**
			 Logs the current user out
			 */
			logOut: function() {
				$localStorage.token = undefined;
				token = undefined;
				service.user = undefined;
				try {
					delete $localStorage.token;
				} catch (e) {}
			},
			/**
			 * Determines if there is a currently logged in user
			 */
			isLoggedIn: function() {
				return service.token() !== undefined;
			},
			/**
			 * Retrieves the token, if one exists
			 */
			token: function() {
				return $localStorage.token || token;
			},
			user: undefined
		};

		//if the user is already logged in, retrieve their information
		if (service.isLoggedIn()) {
			service.user = api.users.get({
				id: service.token().userId
			});
		}
		return service;
	}
]);
angular.module('app').service('api', ['$resource', function($resource) {
	'use strict';
	var users = $resource('/api/users/:id', {}, {
		token: {
			method: 'GET',
			isArray: false,
			url: '/api/users/token'
		}
	});

	var service = {
		users: users
	};
	return service;
}]);