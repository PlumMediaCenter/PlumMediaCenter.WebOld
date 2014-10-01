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
