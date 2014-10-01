angular.module('app').service('account', ['api', '$q', '$localStorage', function (api, $q, $localStorage) {
    'use strict';
    var service = {
        logIn: function (email, password) {
            var deferred = $q.defer();
            var user = api.users.authToken({ email: email, password: password }, function (a, b, c) {
                deferred.resolve(true);
            }, function (a, b, c) { 
                return deferred.reject(false);
            });
            return deferred.promise;
        },
        logOut: function(){

        },
        isLoggedIn: function () {

        }
    };
    return service  ;
}]); 