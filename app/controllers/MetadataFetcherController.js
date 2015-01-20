angular.module('app').controller('MetadataFetcherController', ['Video', '$stateParams', 'notify',
    function(Video, $stateParams, notify) {
        var vm = angular.extend(this, {
            searchBy: 'onlineVideoId',
            fetching: false,
            //api
            search: search,
            generate: generate
        });

        //load the video
        Video.getById($stateParams.videoId).then(function(video) {
            vm.video = video;
        });

        function search() {

        }

        function generate() {
            vm.fetching = true;
            Video.fetchMetadata(vm.video.videoId, vm.onlineVideoId).then(function() {
                debugger;
                Video.getById($stateParams.videoId).then(function(video) {
                    vm.fetching  = false;
                    notify('Found video \'' + vm.video.title + '\'');
                });
            });
        }
    }]);