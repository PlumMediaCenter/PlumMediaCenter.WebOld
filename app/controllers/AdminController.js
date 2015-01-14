angular.module('app').controller('AdminController', ['globals', 'api', 'notify', 'Video',
    function(globals, api, notify, Video) {
        var vm = this;
        vm.generatingLibrary = false;
        globals.title = 'Admin';
        getVideoCounts();
        
        vm.generateLibrary = function() {
            var n = notify('Generating library', 'info');
            vm.generatingLibrary = true;
            console.log(n);
            api.generateLibrary().then(function() {
                notify('Library has been generated', 'success');
            }).catch(function(err) {
                notify('There was an error generating the library: "' + err.message + '"', 'danger');
            }).finally(function() {
                vm.generatingLibrary = false;
                getVideoCounts();
            });
        }

        function getVideoCounts() {
            Video.getCounts().then(function(videoCounts) {
                vm.videoCounts = videoCounts;
            });
        }

    }]);