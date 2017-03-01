angular.module('app').directive('videoPlayer', [function () {
    return {
        restrict: 'E',
        replace: true,
        controllerAs: 'vm',
        controller: ['$scope', 'uniqueId', 'Video', 'globals', Controller],
        bindToController: true,
        scope: {
            videoId: '='
        },
        link: function (scope, element, attributes, vm) {
            vm.element = element;
        }
    };
    function Controller($scope, uniqueId, Video, globals) {
        var vm = this;

        Video.getById(vm.videoId).then(function (video) {
            //get the current progress of this video.
            Video.getProgress(vm.videoId).then(function (seconds) {
                vm.video = video;
                addVideoElement(video, seconds);
            });
        });

        function addVideoElement(video, startSeconds) {
            var id = uniqueId();
            var html = '\
                <video id="' + id + '" class="video-js" vjs-big-play-centered controls style="width:100%;height:100%;"\
                    poster="' + video.hdPosterUrl + '" data-setup=\'{"preload": "auto"}\'>\
                    <source src="' + video.url + '" type="video/mp4" \>\
                    <p class="vjs-no-js">\
                        To view this video please enable JavaScript, and consider upgrading to a web browser that\
                        <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>\
                    </p>\
                 </video>';
            vm.element.append(html);
            //initialize the video player
            var player = videojs(id);

            //register events
            player.ready(function () {
                this.currentTime(startSeconds);
                this.on('timeupdate', function () {
                    updateTime(this.currentTime());
                })
                //when the video finishes, mark it as complete
                this.on('ended', function () {
                    Video.setProgress(vm.video.videoId, -1);
                })
            });
        }

        var lastUpdateTime = new Date();
        function updateTime(currentSeconds) {
            var now = new Date();
            //if it has been at least n seconds since the last time save, save now
            if (now.getTime() - lastUpdateTime.getTime() > 2000) {
                lastUpdateTime = now;
                Video.setProgress(vm.video.videoId, currentSeconds);
            }
        }

        $scope.$on("$destroy", function () {
            globals.hideNavbar = false;
        });
    }

}]);