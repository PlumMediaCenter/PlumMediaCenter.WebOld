angular.module('app').controller('HomeController', ['globals', 'Video', function(globals, Video) {
        var vm = angular.extend(this, {
//            allVideos: [],
//            currentlyLoadedVideos: [],
            //api
//            loadMore: loadMore
        });
//        globals.title = 'Home';
//
//        Video.getAll().then(function(videos) {
//            vm.allVideos = videos;
//        });
//
//        function loadMore() {
//            var numberToLoad = globals.infiniteScrollPageSize;
//            var beginIndex = vm.currentlyLoadedVideos.length;
//            var endIndex = beginIndex + numberToLoad;
//            //if the end index is larger than the list of all videos, change the end index to the length of the list of all videos
//            endIndex = endIndex > vm.allVideos.length ? vm.allVideos.length : endIndex;
//
//            for (var i = beginIndex; i < endIndex; i++) {
//                vm.currentlyLoadedVideos.push(vm.allVideos[i]);
//            }
//        }

    }]);