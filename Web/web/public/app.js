angular.module('app', ['ngResource', 'ngRoute', 'ngStorage']);
debugger;
angular.module('app').controller('LoginController', ['$scope', 'account', function ($scope, account) {
    'use strict';
    var vm = $scope;
    vm.email = 'abc';
    vm.password = 'a';

    vm.logIn = function () {
        account.logIn(vm.email, vm.password).then(function () {
            vm.message = 'Logged in';
        }, function () {
            vm.message = 'Unable to log in'; 
        });
    };
    return vm;
}
]);
angular.module('app').controller('RegisterController', ['$scope', 'account', 'api', function ($scope, account, api) {
    'use strict';
    var vm = this;
    vm.user = {
        firstName: 'Bronley1',
        lastName: 'Plumb',
        email: 'bronley@gmail.com',
        password: 'password',
        passwordConfirm: 'password',
        birthDate: new Date()
    };

    vm.register = function () {
       // api.users.save(vm.user, function () {
       // });
    };
    return vm;
}
]);
angular.module('app').controller('RootController', ['$scope', function ($scope) {
    'use strict';
    var vm = this;
}
]);
/*global document */
angular.module('app')
.directive('logoSm', [function () {
    'use strict';
    var placeholderIsSupported = 'placeholder' in document.createElement('input');
    return {
        template: '<div class="inline-block logo-sm"><span class="logo-img-sm"></span>Plum Video Player</div>'
    };
}]);

angular.module('app')
.directive('logoLg', [function () {
    'use strict';
    var placeholderIsSupported = 'placeholder' in document.createElement('input');
    return {
        template: '<div class="inline-block logo-lg"><span class="logo-img-lg"></span>Plum Video Player</div>'
    };
}]);

/*global document */
angular.module('app').directive('placeholderFallback', [function () {
    'use strict';
    var placeholderIsSupported = 'placeholder' in document.createElement('input');
    return {
        link: function (scope, element, attrs) {
            if (placeholderIsSupported === true) {
                element.css({ display: 'none' });
            } 
        }
    };
}]);
angular.module('app').config(['$routeProvider', '$locationProvider', function ($routeProvider, $locationProvider) {
    'use strict';
    $locationProvider.html5Mode(true);
    $routeProvider
        .when('/login', {
            templateUrl: '/partials/login.html'
        })
        .when('/register', { templateUrl: '/partials/register.html' })
        .otherwise({ 
            redirectTo: '/login'
        })
    ;
}]);
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
angular.module('app').service('api', ['$resource', function ($resource) {
    'use strict';
    var users = $resource('/api/users/:id', {},
        {
            authToken: {
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