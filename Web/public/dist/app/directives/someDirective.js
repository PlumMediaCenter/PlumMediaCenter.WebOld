(function(){
  'use strict';
    angular.module('app').directive('some', [function(){
      return {
        restrict: 'A',
        templateUrl: '/app/directives/someDirective.html',
        link: function(scope, element, attributes){

        }
      };
    }]);
}());
