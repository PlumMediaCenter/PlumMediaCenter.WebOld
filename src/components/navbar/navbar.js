angular.module('app').directive('navbar', ['$state', function ($state) {
    return {
        restrict: 'E',
        controller: ['$state', Controller],
        controllerAs: 'vm',
        bindToController: true,
        templateUrl: 'navbar.html',
        scope: {
        }
    };

    function Controller($state) {
        var vm = angular.extend(this, {
            searchTerm: undefined,
            navbarIsOpen: false,
            //api
            search: search,
            hideNavbar: hideNavbar,
            toggleNavbar: toggleNavbar
        });
        function search() {
            if (vm.searchTerm && vm.searchTerm.trim().length > 0) {
                $state.go('search', { q: vm.searchTerm });
                vm.searchTerm = undefined;
                hideNavbar();
            }
        }

        function showNavbar() {
            vm.navbarIsOpen = true;
        }

        function hideNavbar() {
            vm.navbarIsOpen = false;
        }

        function toggleNavbar() {
            vm.navbarIsOpen ? hideNavbar() : showNavbar();
        }
    }
}]);