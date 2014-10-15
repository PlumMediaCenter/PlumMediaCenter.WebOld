(function() {
	'use strict';
	angular.module('app')
		.controller('AddVideoSourceController', ['api', Controller]);

	function Controller(api) {
		var vm = this;
		vm.videoSource = {};
		vm.save = function() {
			api.videoSources.save(vm.videoSource);
		};
	}
}());