angular.module('app').controller('AdminController', ['globals', 'api', 'notify', 'Video',
    function(globals, api, notify, Video) {
        globals.title = 'Admin';

        var vm = angular.extend(this, {
            //api
            fetchMissingMetadata: fetchMissingMetadata,
            generateLibrary: generateLibrary
        });


        getVideoCounts();

        function generateLibrary() {
            var n = notify('Generating library', 'info');
            globals.generateLibraryIsPending = true;
            api.generateLibrary().then(function() {
                notify('Library has been generated', 'success');
            }).catch(function(err) {
                notify('There was an error generating the library: "' + err.message + '"', 'danger');
            }).finally(function() {
                globals.generateLibraryIsPending = false;
                getVideoCounts();
            });
        }

        function getVideoCounts() {
            Video.getCounts().then(function(videoCounts) {
                vm.videoCounts = videoCounts;
            });
        }

        function fetchMissingMetadata() {
            globals.fetchMissingMetadataIsPending = true;
            notify('Fetching missing metadata', 'info');
            Video.fetchMissingMetadata().then(function() {
                notify('Finished fetching missing metata for videos', 'success');
            }, function() {
                notify('There was an error fetching missing metadata', 'error');
            }).finally(function() {
                globals.fetchMissingMetadataIsPending = false;
            });
        }
    }]);