angular.module('app').directive('videoTile', [function() {
        return {
            restrict: 'E',
            controllerAs: 'vm',
            controller: [Controller],
            bindToController: true,
            scope: {
                video: '='
            },
            templateUrl: 'app/directives/videoTileDirective.html'
        }

        function Controller() {
            
        }
    }]);