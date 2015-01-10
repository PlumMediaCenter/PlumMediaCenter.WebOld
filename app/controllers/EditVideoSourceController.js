angular.module('app').controller('EditVideoSourceController', ['$scope', 'globals', 'VideoSource', '$stateParams','enums',
    function($scope, globals, VideoSource, $stateParams, enums) {
        globals.title = 'Edit Video Source';
        var vm = this;
        vm.reset = reset;
        vm.save = save;
        vm.videoSource = {
            securityType: enums.securityType.public
        };
        vm.originalVideoSource = angular.copy( vm.videoSource);
        //if an id was provided, go look up the settings for that videoSource
        if ($stateParams.id && $stateParams.id > 0) {
            vm.loading = true;
            VideoSource.getById($stateParams.id).then(function(videoSource) {
                vm.videoSource = videoSource;
                vm.originalVideoSource = angular.copy(videoSource);
            }).finally(function() {
                vm.loading = false;
            });
        }

        function reset() {
            vm.videoSource = vm.originalVideoSource;
        }

        function save() {
            VideoSource.save(vm.videoSource).then(function(videoSource) {
                vm.videoSource = videoSource;
            }, function() {
                //handle the error

            });
        }

    }]);