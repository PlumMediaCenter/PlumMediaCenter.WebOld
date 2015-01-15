angular.module('app').controller('SearchController', ['globals', 'Video', '$stateParams', function(globals, Video, $stateParams) {
        var vm = angular.extend(this, {
            currentlyLoadedVideos: [],
            allVideos: [],
            //api
            loadMore: loadMore
        });

        globals.title = 'Home';
        var searchTerm = $stateParams.q;

        Video.search(searchTerm).then(function(videos) {
            vm.allVideos = videos;
        });


        function loadMore() {
            var numberToLoad = 20;
            var beginIndex = vm.currentlyLoadedVideos.length;
            var endIndex = beginIndex + numberToLoad;
            //if the end index is larger than the list of all videos, change the end index to the length of the list of all videos
            endIndex = endIndex > vm.allVideos.length ? vm.allVideos.length : endIndex;

            for (var i = beginIndex; i < endIndex; i++) {
                vm.currentlyLoadedVideos.push(vm.allVideos[i]);
            }
        }

    }]);