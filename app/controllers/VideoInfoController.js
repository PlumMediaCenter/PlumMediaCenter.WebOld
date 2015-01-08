angular.module('app').controller('VideoInfoController', ['globals', 'Video', '$stateParams', function(globals, Video, $stateParams) {
        var vm = this;
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
        })
    }]);