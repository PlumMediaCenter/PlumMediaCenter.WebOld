angular.module('app')
	.directive('logo', [function() {
		'use strict';
		return {
			template: '<div class="inline-block logo"><img src="/content/img/logo.png" class="logo-img"/></span>Plum Video Player</div>'
		};
	}]);