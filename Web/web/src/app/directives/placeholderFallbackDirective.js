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