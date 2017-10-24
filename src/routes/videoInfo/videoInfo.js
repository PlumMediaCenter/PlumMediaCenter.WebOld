angular.module('app').controller('VideoInfoController', ['$scope', '$timeout', 'globals', 'Video', '$state', '$stateParams', 'enums', 'notify',
    function ($scope, $timeout, globals, Video, $state, $stateParams, enums, notify) {
        var vm = angular.extend(this, {
            progressPercent: 0,
            preventCache: $stateParams.preventCache,
            episodes: undefined,
            nextEpisode: undefined,
            videoId: $stateParams.videoId,
            loadMessage: undefined,
            isProcessingVideo: false,
            //api
            getProgressPercentType: getProgressPercentType,
            runtimeMinutes: runtimeMinutes,
            navigateToShow: navigateToShow,
            scanForNewMedia: scanForNewMedia,
            playIsDisabled: playIsDisabled,
            processVideo: processVideo
        });
        globals.title = 'VideoInfo';

        $scope.$watch('vm.episodes', fetchAllEpisodePercentWatched);

        //load the video by id
        Video.getById(vm.videoId).then(function (video) {
            vm.video = video;

            if (vm.video.mediaType === enums.mediaType.show) {
                //get all of the episodes for this show
                Video.getEpisodes(vm.video.videoId).then(function (episodes) {
                    vm.episodes = episodes;
                    //find the next episode that should be watched
                }).then(function () {
                    return Video.getNextEpisode(vm.video.videoId);
                    //select the episode in our local list of episodes that matches the next episode
                }).then(function (nextEpisode) {
                    vm.nextEpisode = _.where(vm.episodes, { videoId: nextEpisode.videoId })[0];
                    //figure out how much of this episode has been watched
                }).then(function () {
                    return Video.getProgressPercent(vm.nextEpisode.videoId);
                    //save the percentWatched to the episode
                }).then(function (percent) {
                    vm.nextEpisode.percentWatched = percent;
                });
            }

            //load the progress of this video
            Video.getProgressPercent(vm.video.videoId).then(function (percent) {
                vm.progressPercent = percent;
            });
        });

        function processVideo() {
            vm.isProcessingVideo = true;
            Video.processVideo(vm.videoId).then(function () {
                notify('Video was processed', 'success');
            }, function () {
                notify('There was a problem processing the video', 'error');
            }).finally(function(){
                vm.isProcessingVideo = false;
            });
        }

        function runtimeMinutes() {
            if (vm.video && vm.video.runtime) {
                return Math.ceil(vm.video.runtime / 60);
            } else {
                return null;
            }
        }

        function playIsDisabled() {
            if (!vm.video || (vm.video.mediaType === enums.mediaType.show && !vm.nextEpisode)) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * Grabs the percent watched for every episode
         * @returns {undefined}
         */
        function fetchAllEpisodePercentWatched() {
            var videoIds = _.pluck(vm.episodes, 'videoId');
            Video.getProgressPercentMultiple(videoIds).then(function (percentObjects) {
                for (var i in percentObjects) {
                    var percentObj = percentObjects[i];
                    var episode = _.where(vm.episodes, { videoId: percentObj.videoId })[0];
                    if (episode) {
                        episode.percentWatched = percentObj.percent;
                    }
                }
            });
        }

        function getProgressPercentType() {
            if (vm.progressPercent < 40) {
                return 'danger';
            } else if (vm.progressPercent < 99) {
                return 'warning';
            } else if (vm.progressPercent < 101) {
                return 'success';
            }
        }

        function scanForNewMedia() {
            vm.loadMessage = 'Scanning for new media';
            Video.scanForNewMedia(vm.videoId).then(function (result) {
                debugger;
                if (!result || result.success !== true) {
                    throw new Error('An error occurred' + JSON.stringify(result));
                }
                if (result && result.newVideoIds && result.newVideoIds.length > 0) {
                    notify(result.newVideoIds.length + ' new media ' + (result.newVideoIds.length === 1 ? 'item was' : 'items were') + ' successfully added', 'success');
                    vm.loadMessage = 'Refreshing page';
                    return $timeout(function () {
                        //reload the current state to get any new videos
                        $state.reload($state.current.name);
                        vm.loadMessage = undefined;
                    }, 1500);
                } else {
                    notify('No new media items were found');
                }

            }, function (error) {
                notify(error.message, 'danger');
            }).finally(function () {
                vm.loadMessage = undefined;
            });
        }

        function navigateToShow() {
            Video.getShowFromEpisodeId(vm.videoId).then(function (show) {
                $state.go('videoInfo', { videoId: show.videoId });
            });
        }
    }]);