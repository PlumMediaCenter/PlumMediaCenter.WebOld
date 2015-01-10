angular.module('app', ['ui.router', 'ui.bootstrap'])
        .run(['$rootScope', 'enums', function($rootScope, enums) {
                $rootScope.enums = enums;
            }]);

fetchConstants().then(bootstrapApplication);


/**
 * Load constants asynchronously BEFORE bootstrapping the application
 * @returns {unresolved}
 */
function fetchConstants() {
    var injector = angular.injector(["ng"])
    var $http = injector.get("$http");
    var $q = injector.get('$q');

    var promises = [
        $q(function(resolve, reject) {
            $http.get('api/GetEnumerations.php').then(function(result) {
                angular.module('app').constant("enums", result.data);
                resolve();
            }, reject);
        })
    ];

    return $q.all(promises);
}

function bootstrapApplication() {
    angular.element(document).ready(function() {
        angular.bootstrap(document, ["app"]);
    });
}