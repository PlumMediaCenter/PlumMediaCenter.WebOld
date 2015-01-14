angular.module('app').controller('VideoInfoController', ['globals', 'Video', '$stateParams', function(globals, Video, $stateParams) {
        var vm = angular.extend(this, {
            progressPercent: 0,
            //api
            getProgressPercentType: getProgressPercentType
        });
        globals.title = 'VideoInfo';

        //load the video by id
        Video.getById($stateParams.videoId).then(function(video) {
            vm.video = video;

            if (vm.video.mediaType === 'TvShow') {
                Video.getEpisodes(vm.video.videoId).then(function(episodes) {
                    vm.episodes = episodes;
                });
                Video.getNextEpisode(vm.video.videoId).then(function(episode) {
                    vm.nextEpisode = episode;
                });
            }

            //this is a show or an episode
            //load the progress of this video
            Video.getProgressPercent(vm.video.videoId).then(function(percent) {
                vm.progressPercent = percent;
            });

        })

        function getProgressPercentType() {
            if (vm.progressPercent < 40) {
                return'danger';
            } else if (vm.progressPercent < 99) {
                return 'warning';
            } else if (vm.progressPercent < 101) {
                return 'success';
            }
        }
    }]);