angular.module('app').directive('videoTile', [function () {
        return {
            restrict: 'E',
            controllerAs: 'vm',
            controller: [Controller],
            bindToController: true,
            scope: {
                video: '='
            },
            templateUrl: '/videoTile.html'
        }

        function Controller() {
            var vm = this;
            //if the video has no poster, use the blank one
            if (vm.video && !vm.video.hdPosterUrl) {
                if (vm.video.mediaType === 'Movie') {
                    vm.video.hdPosterUrl = 'assets/img/posters/BlankPoster.hd.jpg'
                }
            }
        }
    }]);