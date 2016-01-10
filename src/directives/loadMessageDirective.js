angular.module('app').directive('loadMessage', [function () {
    'use strict';
    return {
        restrict: 'E',
        scope: {
            message: '='
        },
        template: '<span ng-show="message !== undefined"><span class="wait-small"></span>&nbsp;{{message}}</span>'
    };
}]);