angular.module('app').controller('AddNewMediaItemController', ['Video', 'globals', 'VideoSource', 'notify', function (Video, globals, VideoSource, notify) {
        globals.title = 'Add new media item';

        var vm = angular.extend(this, {
            //properties
            videoSources: [],
            newMediaItem: {},
            loadMessage: undefined,
            //api
            addNewMediaItem: addNewMediaItem
        });

        VideoSource.getAll().then(function (videoSources) {
            vm.videoSources = videoSources;
        });

        function addNewMediaItem() {
            vm.loadMessage = 'Scanning for new media';
            Video.addNewMediaItem(vm.newMediaItem.videoSourceId, vm.newMediaItem.path).then(function (result) {
                if (!result || result.success !== true) {
                    throw new Error('An error occurred' + JSON.stringify(result));
                }
                if (result && result.newVideoIds && result.newVideoIds.length > 0) {
                    notify(result.newVideoIds.length + ' new media ' + (result.newVideoIds.length === 1 ? 'item was' : 'items were') + ' successfully added', 'success');
                } else {
                    notify('No new media items were found');
                }
            }, function (error) {
                notify(error.message, 'danger');
            }).finally(function () {
                vm.loadMessage = undefined;
            });
        }
    }]);