angular.module('app').controller('AdminController', ['globals', 'api', '$modal',
    function(globals, api, $modal) {
        var vm = this;
        globals.title = 'Admin';

        vm.generateLibrary = function() {
            api.generateLibrary().then(function() {
                alert('generated');
            });
        }



    }]);