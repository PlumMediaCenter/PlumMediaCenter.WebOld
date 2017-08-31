angular.module('app').controller('MetadataFetcherController', ['$scope', '$q', 'globals', 'refreshImage', 'Video', '$state', '$stateParams', 'notify', 'enums',
    function($scope, $q, globals, refreshImage, Video, $state, $stateParams, notify, enums) {
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
            } else {
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
                return Video.getById(vm.video.videoId);
            }).then(function(video) {
                //refresh the posters so that when we go back to videoInfo, the poster cache has been cleared
                return refreshImage(video.sdPosterUrl).then(function() {
                    return refreshImage(video.hdPosterUrl);
                }).then(function(){
                    //there was an issue getting the browser to refresh the cached images. try reloading the page (after we have 
                    //navigated to the videoInfo page)
                    setTimeout(function(){
                        window.location.reload();
                    }, 200);
                    return undefined;
                }, function(err){
                    return $q.reject(err);
                });
            }).then(function() {
                vm.metadataResults = undefined;
                vm.metadataIsBeingFetched = false;

                notify('Updated video with selected metadata', 'success');
                $state.go('videoInfo', {videoId: vm.videoId});
            })['catch'](function(err) {
                vm.metadataIsBeingFetched = false;
                notify('There was an error fetching metadata for the video you selected: ' + err, 'error');
            })
        }


    }]);