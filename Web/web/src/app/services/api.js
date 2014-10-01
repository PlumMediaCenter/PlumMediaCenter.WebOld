angular.module('app').service('api', ['$resource', function ($resource) {
    'use strict';
    var users = $resource('/api/users/:id', {},
        {
            authToken: {
                method: 'GET',
                isArray: false,
                url: '/api/users/:email/token'
            }
        });

    var service = { 
        users: users
    }; 
    return service;
}]);