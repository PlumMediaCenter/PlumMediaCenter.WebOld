angular.module('app').controller('AdminController', ['globals', 'api', 'notify',
    function(globals, api, notify) {
        var vm = this;
        vm.generatingLibrary = false;
        globals.title = 'Admin';

        vm.generateLibrary = function() {
            var n = notify('Generating library', 'info');
            vm.generatingLibrary = true;
            console.log(n);
            api.generateLibrary().then(function() {
                notify('Library has been generated', 'success');
            }).catch(function(err) {
                notify('There was an error generating the library: "' + err.message + '"', 'danger');
            }).finally(function(){
                vm.generatingLibrary = false;
            });
        }



    }]);