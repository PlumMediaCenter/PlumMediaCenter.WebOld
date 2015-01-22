angular.module('app').controller('EditVideoSourceController', ['$stateParams',
    function($stateParams) {
        var vm = angular.extend(this, {
            //properties
            title: undefined
                    //api

        });

        vm.title = $stateParams.title;

    }]);