angular.module('app').controller('NavbarController', ['$state', function($state) {
        var vm = angular.extend(this, {
            searchTerm: undefined, 
            //api
            search: search
        });

        function search() {
            $state.go('search', {q: vm.searchTerm});
        }

    }]);