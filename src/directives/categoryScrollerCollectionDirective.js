angular.module('app').directive('categoryScrollerCollection', [function () {
        return {
            restrict: 'E',
            controller: ['Video', Controller],
            controllerAs: 'vm',
            bindToController: true,
            templateUrl: 'categoryScrollerCollectionDirective.html',
            link: function () {

            }
        };

        function Controller(Video) {
            var vm = angular.extend(this, {
                categories: undefined
            });
            //get all of the category names
            Video.getCategoryNames().then(function (names) {
                 vm.categoryNames = names;
            });
        }
    }
]);