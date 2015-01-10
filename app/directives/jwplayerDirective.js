angular.module('app').directive('jwplayer', ['uniqueId', function(uniqueId) {
        return {
            restrict: 'E',
            replace: true,
            controllerAs: 'vm',
            controller: ['Video', '$scope', 'util', Controller],
            bindToController: true,
            scope: {
                videoId: '='
            },
            link: function(scope, element, attributes, vm) {
                vm.elementId = element.attr('id');
                vm.playlist = [{file: 'http://localhost:8080/videos/movies/A%20Good%20Day%20to%20Die%20Hard/A%20Good%20Day%20to%20Die%20Hard.mp4'}];
                if (!vm.elementId) {
                    vm.elementId = uniqueId();
                }
                
                element.attr('id', vm.elementId);
//                //anytime the window is resized, resize the player accordingly
//                $(window).resize(function() {
//                    //get the current width of the containerRelativer,set the jwplayer to that size
//                    var w = element.width();
//                    var h = element.parent().height();
//                                        debugger;
//
//                    vm.player.resize(w, h); 
//                });
            },
            template: '<div class="jwplayer"></div>'
        }

        function Controller(Video, $scope, util) {
            var vm = this;
            //create a new playlist
            vm.playlist = [];
            vm.loadVideo = loadVideo;

            //load the video
            Video.getById(vm.videoId).then(function(video) {
                vm.video = video;
            });

            $scope.$watch('vm.video', vm.loadVideo);

            //when the directive is removed, remove the jwplayer from the page
            $scope.$on('$destroy', function() {
                jwplayer(vm.elementId).remove();
            });


            function loadVideo(video) {
                //empty the playlist 
                util.blankItemInPlace(vm.playlist);

                if (video) {
                    //add the video to the playlist
                    vm.playlist.push({file: video.url});

                    if (vm.jwplayer) {
                        vm.player.load(vm.playlist);
                    } else {
                        //the jwplayer has not yet been created, create it now
                        jwplayer(vm.elementId).setup({
                            flashplayer: 'lib/jwplayer-6.11/jwplayer.flash.swf',
                            primary: 'html5',
                            playlist: vm.playlist,
                            startparam: 'start',
                            wmode: 'transparent',
                            width: '100%',
                            height: '100%',
                            autostart: true
                        });
                        vm.player = jwplayer(vm.elementId);
                    }
                }
            }
        }
    }]);