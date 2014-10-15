angular.module('app')
	.directive('loading', [function () {
	    'use strict';
	    return {
	        replace: true,
	        scope: {
                message: '=loading'
	        },
	        template: '<div class="loading-message" ng-show="message !== undefined"><img src="/content/img/ajax-loader.gif"/>{{message}}</div>'
	    };
	}]); 