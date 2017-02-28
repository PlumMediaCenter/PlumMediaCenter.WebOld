angular.module('app').controller('VideoSourcesController', ['globals', 'VideoSource', 'notify',
    function(globals, VideoSource, notify) {
        var vm = this;
        vm.editIsVisible = false;
        vm.deleteVideoSource = deleteVideoSource;
        vm.refresh = loadVideoSources;

        globals.title = 'Video Sources';
        loadVideoSources();
        
        function loadVideoSources() {
            VideoSource.getAll().then(function(videoSources) {
                vm.videoSources = videoSources;
            });
        }

        function deleteVideoSource(id) {
            VideoSource.deleteById(id).then(loadVideoSources);
        }
    }]);