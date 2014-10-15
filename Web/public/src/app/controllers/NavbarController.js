(function() {
	'use strict';
	angular.module('app')
		.controller('NavbarController', ['account', Controller]);

	function Controller(account) {
		var vm = this;
		vm.user = account.user;
	}
}());