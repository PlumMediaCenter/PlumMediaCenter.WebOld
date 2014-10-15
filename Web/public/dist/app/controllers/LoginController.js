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

		vm.logIn = function () {
		    vm.error = undefined;
		    vm.message = 'Logging in';
			account.logIn(vm.email, vm.password, vm.rememberMe).then(function() {
				var urlDestination = $location.search().url;
				//if the user was trying to get somewhere else before we redirected them, send them there now.
				urlDestination = urlDestination === undefined ? '/dashboard' :
					urlDestination;
				$location.url(urlDestination);
			}, function () {
			    vm.message = undefined;
				vm.error = 'Invalid email or password';
			});
		};
		return vm;
	}
}());