(function() {
	'use strict';
	angular.module('app')
		.controller('VideoSourcesController', ['api', Controller]);

	function Controller(api) {
		var vm = this;
		vm.videoSources = api.videoSources.query();
	}
}());