angular.module('app').controller('PlayController', ['$scope', 'globals', '$stateParams', 'notify',
    function($scope, globals, $stateParams, notify) {
        globals.title = 'Play';
        globals.hideNavbar = true;

        var vm = this;
        vm.videoId = $stateParams.videoId;

        $scope.$on("$destroy", function() {
           globals.hideNavbar = false;
        });
    }]);