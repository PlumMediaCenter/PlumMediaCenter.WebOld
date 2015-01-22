angular.module('app').controller('MetadataFetcherController', ['$scope', 'Video', '$stateParams', 'notify', 'enums',
    function($scope, Video, $stateParams, notify, enums) {
        var vm = angular.extend(this, {
            searchByOptions: {
                onlineVideoId: 'onlineVideoId',
                title: 'title'
            },
            searchBy: 'onlineVideoId',
            isSearching: false,
            textboxLabel: undefined,
            //this is the value (title, onlineVideoId) to use to search for the metadata
            searchValue: undefined,
            searchResults: undefined,
            //api
            search: search,
            generate: generate,
            calculateTextboxLabel: calculateTextboxLabel
        });

        $scope.$watch('vm.searchBy', vm.calculateTextboxLabel);
        $scope.$watch('vm.video', vm.calculateTextboxLabel);

        function calculateTextboxLabel() {
            vm.textboxLabel = undefined;

            if (!vm.video) {
                return;
            }
            if (vm.searchBy === vm.searchByOptions.title) {
                vm.textboxLabel = 'Title';
            } else {
                vm.textboxLabel = vm.video.mediaType === enums.mediaTypeMovie ? 'TMDB ID' : 'TVDB ID';
            }
        }

        //load the video
        Video.getById($stateParams.videoId).then(function(video) {
            vm.video = video;
        });

        function search() {
            vm.isSearching = true;
            var promise;
            if (vm.searchBy === vm.searchByOptions.title) {
                promise = Video.getMetadataSearchResultsByTitle(vm.video.mediaType, vm.searchValue);
            } else {
                promise = Video.getMetadataSearchResultsByOnlineVideoId(vm.video.mediaType, vm.searchValue);
            }
            promise.then(function(searchResults){
                vm.metadataResults = searchResults;
                vm.isSearching = false;
            });
        }

        function generate() {
            Video.fetchMetadata(vm.video.videoId, vm.onlineVideoId).then(function() {
                debugger;
                Video.getById($stateParams.videoId).then(function(video) {

                    notify('Found video \'' + vm.video.title + '\'');
                });
            });
        }
    }]);