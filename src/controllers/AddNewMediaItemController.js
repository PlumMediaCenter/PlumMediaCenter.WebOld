angular.module('app').controller('AddNewMediaItemController', ['Video', 'globals', 'VideoSource', 'notify', function (Video, globals, VideoSource, notify) {
        globals.title = 'Add new media item';

        var vm = angular.extend(this, {
            //properties
            videoSources: [],
            newMediaItem: {},
            //api
            addNewMediaItem: addNewMediaItem
        });

        VideoSource.getAll().then(function (videoSources) {
            vm.videoSources = videoSources;
        });

        function addNewMediaItem() {
            Video.addNewMediaItem(vm.newMediaItem.videoSourceId, vm.newMediaItem.path).then(function (result) {
                notify('New media items were successfully added', 'success');
            }, function (error) {
                notify(error.message, 'danger');
            });
        }
    }]);