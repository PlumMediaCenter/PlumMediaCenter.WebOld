angular.module('app').directive('videoTile', [function() {
        return {
            restrict: 'E',
            controllerAs: 'vm',
            controller: [Controller],
            bindToController: true,
            scope: {
                video: '='
            },
            templateUrl: 'videoTileDirective.html'
        }

        function Controller() {
            
        }
    }]);