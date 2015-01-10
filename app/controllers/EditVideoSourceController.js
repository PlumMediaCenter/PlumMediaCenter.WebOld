angular.module('app').controller('EditVideoSourceController', ['globals', 'VideoSource', '$stateParams', 
    function(globals, VideoSource,$stateParams) {
        var vm = this;
        globals.title = 'Edit Video Source';
        vm.videoSource = {};
        //if an id was provided, go look up the settings for that videoSource
        if ($stateParams.id) {
            vm.loading = true;
            VideoSource.getById($stateParams.id).then(function(videoSource) {
                vm.videoSource = videoSource;
            }).finally(function(){
                vm.loading = false;
            });
        }
    }]);