
angular.module('app').controller('LoginController', ['$scope', '$location',
	'account',
	function($scope, $location, account) {
		'use strict';
		var vm = $scope;
		vm.email = 'bronley@gmail.com';
		vm.password = 'pass';
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
				debugger;
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
]);