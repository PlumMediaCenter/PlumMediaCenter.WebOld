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

                //TODO - implement the keyboard shortcuts
                function keyboardShortcuts(e) {
                    switch (e.which) {
                        case 32://spacebar key
                            //toggle playback
                            jwplayer().play();
                            break;
                        case 70: //f key
                            //toggle fullscreen
                            if (player.getFullscreen() === true) {
                                player.setFullscreen(false);
                            } else {
                                player.setFullscreen(true);
                            }
                            break;
                        case 39: //right arrow key
                            //seek forward n seconds
                            var position = player.getPosition();
                            var newPosition = position + seekBurstSeconds;
                            if (position <= seekPosition) {
                                newPosition = seekPosition + seekBurstSeconds;
                            }
                            seekPosition = newPosition;
                            player.seek(seekPosition);
                            break;
                        case 37: //left arrow key
                            //seek backwards n seconds
                            var position = player.getPosition();
                            var newPosition = position - seekBurstSeconds;
                            if (position >= seekPosition) {
                                newPosition = seekPosition - seekBurstSeconds;
                            }
                            seekPosition = newPosition;
                            player.seek(seekPosition);
                            break;
                    }
                }
            },
            template: '<div class="jwplayer"></div>'
        }

        function Controller(Video, $scope, util) {
            var vm = this;
            angular.extend(this,
                    {
                        play: play,
                        pause: pause,
                        playlist: [],
                        loadVideo: loadVideo,
                        togglePlayback: togglePlayback
                    }
            );

            //keeps track of the number of seconds that have passed since the video has saved its position in the database
            var playPositionUpdateTime = new Date();

            //load the video
            Video.getById(vm.videoId).then(function(video) {
                vm.video = video;
            });

            $scope.$watch('vm.video', vm.loadVideo);

            //when the directive is removed, remove the jwplayer from the page
            $scope.$on('$destroy', function() {
                jwplayer(vm.elementId).remove();
            });

            /**
             * Toggles the play/pause state. if playing, the player pauses. if paused, the player plays.
             */
            function togglePlayback() {
                vm.player.play();
            }

            /**
             * Tells the player to play. If already playing, playback continues. 
             * if paused, the player starts playing.
             */
            function play() {
                vm.player.play(true);
            }

            /**
             * Pauses the player. If the player is playing, playback is paused. 
             * if the player is paused, the player stays paused.
             */
            function pause() {
                vm.player.pause(false);
            }

            function loadVideo(video) {
                //empty the playlist 
                util.blankItemInPlace(vm.playlist);
                if (!video) {
                    return;
                }
                //add the video to the playlist
                vm.playlist.push({
                    file: video.url,
                    image: video.posterUrl,
                    title: video.title,
                    video: video
                });

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
                        events: {
                            onTime: onTime,
                            onPlay: onPlay
                        },
                        autostart: true
                    });
                    vm.player = jwplayer(vm.elementId);
                }
            }

            var startVideoWhereWeLeftOffProcessed = false;
            /**
             * Event that is called every time the video changes time position. This may be called up to 
             * 10 times a second
             */
            function onTime(obj) {
                if (startVideoWhereWeLeftOffProcessed === false && obj.position > 0) {
                    startVideoWhereWeLeftOffProcessed = true;
                    startVideoWhereWeLeftOff();
                }

                var positionInSeconds = obj.position;
                //every so often, update the database with the current video's play position
                var nowTime = new Date();
                var timeSinceLastUpdate = nowTime - playPositionUpdateTime;
                if (timeSinceLastUpdate > 1000) {
                    playPositionUpdateTime = new Date();
                    Video.setProgress(vm.video.videoId, positionInSeconds);
                }
            }

            /**
             * Seeks to the playback position indicated by the database. This should only be called ONCE, 
             * and only after the video has started playing
             */
            function startVideoWhereWeLeftOff() {
                //seek the player to the startPosition
                //if a startSeconds value greater than 0 was provided, seek to that position in the video
                if (startSeconds > 0) {
                    player.seek(startSeconds);
                }
            }

            /**
             * Event that is fired every time the video starts playing
             */
            function onPlay() {
                playPositionUpdateTime = new Date();
            }

        }
    }]);