angular.module('app').service('api', ['$resource', function($resource) {
	'use strict';
	var users = $resource('/api/users/:id', {}, {
		token: {
			method: 'GET',
			isArray: false,
			url: '/api/users/token'
		}
	});

	var videoSources = $resource('/api/videoSources/:id');

	var service = {
		users: users,
		videoSources: videoSources
	};
	return service;
}]);