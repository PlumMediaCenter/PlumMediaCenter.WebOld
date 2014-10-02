angular.module('app', ['ngResource', 'ngRoute', 'ngStorage']);

angular.module('app').controller('BaseController', [ function () {
    'use strict';
    var vm = this;
      
}]);
angular.module('app').controller('DashboardController', [ function () {
    'use strict';
    var vm = this;
      
}]);
angular.module('app').controller('LoginController', ['$scope', '$location','account', function ($scope, $location, account) {
    'use strict';
    var vm = $scope;
    vm.email = 'bronley@gmail.com';
    vm.password = 'pass';
    vm.rememberMe = true;
    
    //clear the error message after any change
    $scope.$watchCollection(function () {
        return [vm.email, vm.password, vm.rememberMe];
    }, function () { 
        vm.error = undefined;
    });

    vm.logIn = function () {
        vm.error = undefined;
        account.logIn(vm.email, vm.password, vm.rememberMe).then(function () {
            debugger;
            var urlDestination = $location.search().url;
            //if the user was trying to get somewhere else before we redirected them, send them there now.
            urlDestination = urlDestination === undefined? '/dashboard': urlDestination;
            $location.url(urlDestination);
        }, function () {
            vm.error = 'Invalid email or password'; 
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
    }).when('/dashboard', {
        templateUrl: '/partials/dashboard.html'
    }).when('/register', {
        templateUrl: '/partials/register.html'
    }).otherwise({
        redirectTo: '/dashboard'
    })
;
}]).run(['$rootScope', '$location', 'account', '$log', function ($rootScope, $location, account, $log) {
    'use strict'; 
    $rootScope.$on('$routeChangeStart', function (event, next, current) {
        //if the user is not currently logged in, redirect them to the login screen
        if (!account.isLoggedIn()) {
            $log.debug('User is not logged in. Redirecting to the login screen');

            //if the user is not already headed to the login screen, send them there
            if (next.$$route.templateUrl !== '/partials/login.html') {
                var search = { url: $location.url() };
                $location.path('/login').search(search);
            }
        }
    });
}]);
angular.module('app').service('account', ['api', '$q', '$localStorage', function (api, $q, $localStorage) {
    'use strict';
    var token;
    var service = {
        /**
         Attempts to get an authentication token from the server. If remember me is provided, the token is saved in local storage
         and is used in future page loads. If false, the user is only logged in for this session
         */
        logIn: function (email, password, rememberMe) {
            //log out whoever is currently logged in
            service.logOut();
            
            var deferred = $q.defer();
            api.users.token({ email: email, password: password }, function (authToken, b, c) {
                debugger;
                if (rememberMe === true) {
                    $localStorage.token = authToken;
                } else {
                    token = authToken;
                }
                deferred.resolve(true);
            }, function (a, b, c) {
                return deferred.reject(false);
            });
            return deferred.promise;
        },
        /**
         Logs the current user out 
         */
        logOut: function () {
            debugger;
            $localStorage.token = undefined;
            token = undefined;
            try {
                delete $localStorage.token;
            } catch (e) { }
        },
        /**
         * Determines if there is a currently logged in user
         */
        isLoggedIn: function () {
            return service.token() !== undefined;
        },
        /**
         * Retrieves the token, if one exists
         */
        token: function () {
            return $localStorage.token || token;
        }
    };
    return service;
}]); 
angular.module('app').service('api', ['$resource', function ($resource) {
    'use strict';
    var users = $resource('/api/users/:id', {},
        {
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