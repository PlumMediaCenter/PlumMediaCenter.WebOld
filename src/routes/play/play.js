angular.module('app').controller('PlayController', ['$scope', 'globals', '$stateParams', 'notify',
    function($scope, globals, $stateParams, notify) {
        globals.title = 'Play';
        globals.hideNavbar = true;
        
        var vm = angular.extend(this, {
            videoId: $stateParams.videoId,
            showVideoId: $stateParams.showVideoId
        });

        $scope.$on("$destroy", function() {
           globals.hideNavbar = false;
        });
    }]);