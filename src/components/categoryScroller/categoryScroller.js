angular.module('app').directive('categoryScroller', ['$window', '$timeout', 'debounce', function ($window, $timeout, debounce) {
        var id = 0;
        return {
            restrict: 'E',
            controller: ['$scope', 'Video', Controller],
            controllerAs: 'vm',
            bindToController: true,
            templateUrl: 'categoryScroller.html',
            scope: {
                category: '=?',
                categoryName: '=?'
            },
            link: function ($scope, element, attributes, vm) {
                var myId = id++;
                //anytime the window changes size, determine the new width of this element
                angular.element($window).bind('resize', function () {
                    calculateElementWidth();
                });

                function calculateElementWidth() {
                    console.log('debouncing ' + myId);
                    debounce(myId, function () {
                        console.log('calling ' + myId);
                        if(element) {
                            var rect = element[0].getBoundingClientRect();
                            vm.width = rect.width;
                        }
                    }, 100);
                }

                //anytime the width changes, calculate the size of the first video tile in the list
                $scope.$watchCollection(function () {
                    return vm.width;
                }, function (width) {
                    getTileWidth();
                });

                function getTileWidth() {
                    //get the first video tile
                    var videoTile = element[0].querySelector('video-tile');
                    if(videoTile) {
                        var rect = videoTile.getBoundingClientRect();
                        vm.videoTileWidth = rect.width;
                        vm.videoTileHeight = rect.height;
                    }
                }
                //the first time the length of the video list is greater than zero, recalculate the tile width
                $scope.$watch(function () {
                    return vm.category && vm.category.videos && vm.category.videos.length ? true : false;
                }, function (newValue, oldValue) {
                    if (newValue) {
                        getTileWidth();
                    }
                });

                calculateElementWidth();

            }
        };

        function Controller($scope, Video) {
            var vm = angular.extend(this, {
                category: this.category,
                direction: undefined,
                width: 0,
                videoTileWidth: 0,
                videoTileHeight: 0,
                visibleVideoTileCount: 0,
                visibleVideos: [],
                leftmostVideoIndex: 0,
                //api
                calculateVisibleVideoTileCount: calculateVisibleVideoTileCount,
                getLocationText: getLocationText,
                populateVisibleVideos: populateVisibleVideos,
                pageLeft: pageLeft,
                pageRight: pageRight,
                showPageLeft: showPageLeft,
                showPageRight: showPageRight,
                videoCount: videoCount
            });

            //anytime the categoryName changes, reload the video list
            $scope.$watch(function () {
                return vm.categoryName;
            }, function (newValue, oldValue) {
                if (newValue) {
                    Video.getCategories([newValue]).then(function (categories) {
                        vm.category = categories[0];
                        populateVisibleVideos();
                    });
                }
            });

            $scope.$watch(function () {
                return vm.videoTileWidth;
            }, function () {
                vm.calculateVisibleVideoTileCount();
            });
            $scope.$watch(function () {
                return vm.width;
            }, function () {
                vm.calculateVisibleVideoTileCount();
            });

            $scope.$watch(function () {
                return vm.visibleVideoTileCount;
            }, function (newVisibleVideoTileCount, oldVisibleVideoTileCount) {
                populateVisibleVideos();
            });

            function populateVisibleVideos() {
                if (vm.visibleVideoTileCount < 1) {
                    vm.visibleVideos = [];
                    return;
                }
                if (!vm.category) {
                    return;
                }
                //if the list of videos is smaller than the maximum displayable, then just add all of them
                if (vm.videoCount() <= vm.visibleVideoTileCount) {
                    vm.visibleVideos = vm.category.videos.slice(0);
                } else {
                    //find the index of the leftmost video
                    var endIndex = vm.leftmostVideoIndex + vm.visibleVideoTileCount;
                    vm.visibleVideos = [];
                    for (var i = vm.leftmostVideoIndex; i < endIndex; i++) {
                        var index = i % vm.videoCount();
                        var video = vm.category.videos[index];
                        if (video) {
                            vm.visibleVideos.push(video);
                        }
                    }
                }
            }

            function calculateVisibleVideoTileCount() {
                //60px for the left and right navigation buttons
                var num = (vm.width - 60) / vm.videoTileWidth;
                if (!isFinite(num)) {
                    num = 0;
                } else {
                    num = Math.floor(num);
                }
                vm.visibleVideoTileCount = num;
            }

            function pageLeft() {
                var newLeftmostIndex = vm.leftmostVideoIndex - vm.visibleVideoTileCount;
                if (newLeftmostIndex < 0) {
                    newLeftmostIndex = 0;
                }
                vm.leftmostVideoIndex = newLeftmostIndex;
                vm.populateVisibleVideos();
                vm.direction = 'left';
            }

            function pageRight() {
                var newLeftmostIndex = vm.leftmostVideoIndex + vm.visibleVideoTileCount;
                var maxLeftmostIndex = (vm.videoCount() + 1) - vm.visibleVideoTileCount;
                if (newLeftmostIndex > maxLeftmostIndex) {
                    newLeftmostIndex = maxLeftmostIndex;
                }
                vm.leftmostVideoIndex = newLeftmostIndex;
                console.log('leftmost idx' + vm.leftmostVideoIndex);
                vm.populateVisibleVideos();
                vm.direction = 'right';
            }

            function videoCount() {
                return vm.category && vm.category.videos ? vm.category.videos.length : 0;
            }

            function showPageLeft() {
                return vm.leftmostVideoIndex > 0
            }

            function showPageRight() {
                var maxLeftmostIndex = (vm.videoCount() - 1) - vm.visibleVideoTileCount;
                return vm.leftmostVideoIndex < maxLeftmostIndex;
            }

            function getLocationText() {
                return "";
                var firstNumber = vm.leftmostVideoIndex = 0 ? 0 : (vm.leftmostVideoIndex + 1);
                var text = firstNumber + '-' + (vm.leftmostVideoIndex + vm.visibleVideos.length) + ' of ' + vm.videoCount();
                return text;
            }
        }
    }
]);