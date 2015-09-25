angular.module('app').directive('categoryScroller', ['$window', '$timeout', 'debounce', function ($window, $timeout, debounce) {
        var id = 0;
        return {
            restrict: 'E',
            controller: ['$scope', Controller],
            controllerAs: 'vm',
            bindToController: true,
            templateUrl: 'categoryScrollerDirective.html',
            scope: {
                category: '='
            },
            link: function ($scope, element, attributes, vm) {
                var myId = id++;
                //anytime the window changes size, determine the new width of this element
                angular.element($window).bind('resize', function () {
                    calculateElementWidth();
                });
                function calculateElementWidth() {
                    console.log('debouncing ' + myId);
                    debounce(vm, function () {
                        console.log('calling ' + myId);
                        var rect = element[0].getBoundingClientRect();
                        vm.width = rect.width;
                    }, 300);
                }

                //anytime the width changes, calculate the size of the first video tile in the list
                $scope.$watch(function () {
                    return vm.width;
                }, function (width) {
                    //get the first video tile
                    var videoTile = element[0].querySelector('video-tile');
                    var rect = videoTile.getBoundingClientRect();
                    vm.videoTileWidth = rect.width;
                });

                calculateElementWidth();

            }
        };

        function Controller($scope) {
            var vm = angular.extend(this, {
                category: this.category,
                width: 0,
                videoTileWidth: 0,
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
                showPageRight: showPageRight
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
                //find the index of the leftmost video
                var endIndex = vm.leftmostVideoIndex + vm.visibleVideoTileCount;
                vm.visibleVideos = [];
                for (var i = vm.leftmostVideoIndex; i < endIndex; i++) {
                    var index = i % vm.category.videos.length;
                    var video = vm.category.videos[index];
                    vm.visibleVideos.push(video);
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
            }

            function pageRight() {
                var newLeftmostIndex = vm.leftmostVideoIndex + vm.visibleVideoTileCount;
                var maxLeftmostIndex = (vm.category.videos.length + 1) - vm.visibleVideoTileCount;
                if (newLeftmostIndex > maxLeftmostIndex) {
                    newLeftmostIndex = maxLeftmostIndex;
                }
                vm.leftmostVideoIndex = newLeftmostIndex;
                console.log('leftmost idx' + vm.leftmostVideoIndex);
                vm.populateVisibleVideos();
            }

            function showPageLeft() {
                return vm.leftmostVideoIndex > 0
            }

            function showPageRight() {
                var maxLeftmostIndex = (vm.category.videos.length - 1) - vm.visibleVideoTileCount;
                return vm.leftmostVideoIndex < maxLeftmostIndex;
            }

            function getLocationText() {
                return (vm.leftmostVideoIndex + 1) + '-' + (vm.leftmostVideoIndex + vm.visibleVideos.length) + ' of ' + vm.category.videos.length;
            }
        }
    }
]);