angular.module('app').controller('MetadataFetcherController', ['$scope', 'globals', 'Video', '$state', '$stateParams', 'notify', 'enums',
    function($scope, globals, Video, $state, $stateParams, notify, enums) {
        globals.title = 'Fetch Metadata';
        var vm = angular.extend(this, {
            searchByOptions: {
                onlineVideoId: 'onlineVideoId',
                title: 'title'
            },
            videoId: $stateParams.videoId,
            searchBy: 'onlineVideoId',
            isSearching: false,
            metadataIsBeingFetched: false,
            textboxLabel: undefined,
            //this is the value (title, onlineVideoId) to use to search for the metadata
            searchValue: undefined,
            searchResults: undefined,
            video: {},
            //api
            search: search,
            calculateTextboxLabel: calculateTextboxLabel,
            fetchMetadataByOnlineVideoId: fetchMetadataByOnlineVideoId
        });

        $scope.$watch('vm.searchBy', searchByChanged);

        $scope.$watch('vm.video', vm.calculateTextboxLabel);

        function searchByChanged() {
            vm.calculateTextboxLabel();
            if (vm.searchBy === vm.searchByOptions.title) {
                vm.searchValue = vm.video.title;
            }else{
                vm.searchValue = '';
            }
        }

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
        Video.getById(vm.videoId).then(function(video) {
            angular.extend(vm.video, video);
            searchByChanged()
        });

        Video.getPathInfo(vm.videoId).then(function(video) {
            angular.extend(vm.video, video);
        });

        function search() {
            vm.isSearching = true;
            var promise;
            if (vm.searchBy === vm.searchByOptions.title) {
                promise = Video.getMetadataSearchResultsByTitle(vm.video.mediaType, vm.searchValue);
            } else {
                promise = Video.getMetadataSearchResultsByOnlineVideoId(vm.video.mediaType, vm.searchValue);
            }
            promise.then(function(searchResults) {
                vm.metadataResults = searchResults;
                vm.isSearching = false;
            });
        }

        function fetchMetadataByOnlineVideoId(onlineVideoId) {
            vm.metadataIsBeingFetched = true;
            Video.fetchMetadata(vm.video.videoId, onlineVideoId).then(function() {
                vm.metadataResults = undefined;
                vm.metadataIsBeingFetched = false;
                $state.go('videoInfo', {videoId: vm.videoId, preventCache: true});
                notify('Updated video with selected metadata', 'success');
            }, function(err) {
                vm.metadataIsBeingFetched = false;
                notify('There was an error fetching metadata for the video you selected', 'error');
            });
        }
    }]);