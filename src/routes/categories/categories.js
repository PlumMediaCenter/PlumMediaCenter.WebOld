angular.module('app').controller('CategoriesController', ['Video', 'globals', '$stateParams',
    function (Video, globals, $stateParams) {
        globals.title = 'Add new media item';

        var vm = angular.extend(this, {
            //properties
            categoryName: $stateParams.categoryName,
            loadMessage: undefined,
            category: undefined,
        });

        (function constructor() {
            vm.loadMessage = 'Loading videos';
            Video.getCategories([vm.categoryName]).then(function (categories) {
                vm.category = categories[0];
            }).finally(function () {
                vm.loadMessage = undefined;
            });
        })();
    }
]);