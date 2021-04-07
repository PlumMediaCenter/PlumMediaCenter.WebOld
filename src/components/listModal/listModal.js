angular.module('app').directive('listModal', ['Video', function (Video) {
    return {
        restrict: 'E',
        controller: ['$scope', 'Video', Controller],
        controllerAs: 'vm',
        bindToController: true,
        templateUrl: '/listModal.html',
        scope: {
            videoId: '=',
            onHide: '&'
        },
        link: function ($scope, element, attributes, vm) {
            $("#myModal").modal('show');
            $("#myModal").on('hide.bs.modal', function (e) {
                $scope.$apply(function () {
                    if (vm.onHide) {
                        vm.onHide();
                    }
                });
            });
        }
    };

    function Controller($scope, Video) {
        var vm = angular.extend(this, {
            toggleList: toggleList,
            loadListInfo: function () {
                if (vm.videoId) {
                    return Video.getListInfo(vm.videoId).then((value) => {
                        vm.listInfo = value;
                    });
                }
            }
        });

        $scope.$watch('vm.videoId', () => {
            vm.loadListInfo();
        });

        function toggleList(listName) {
            var promise;
            if (this.listInfo[listName]) {
                promise = Video.removeFromList(listName, vm.videoId);
            } else {
                promise = Video.addToList(listName, vm.videoId);
            }
            //switch the value locally for now so the user sees an instant change
            this.listInfo[listName] = !this.listInfo[listName];
            return promise.then(function () {
                return vm.loadListInfo();
            }, console.error);
        }
    }
}
]);
