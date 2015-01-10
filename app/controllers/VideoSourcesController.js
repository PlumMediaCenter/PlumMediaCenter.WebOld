angular.module('app').controller('VideoSourcesController', ['globals', 'VideoSource', 'notify',
    function(globals, VideoSource, notify) {
        var vm = this;
        vm.editIsVisible = false;

        globals.title = 'Video Sources';

        VideoSource.getAll().then(function(videoSources) {
            vm.videoSources = videoSources;
        });


    }]);