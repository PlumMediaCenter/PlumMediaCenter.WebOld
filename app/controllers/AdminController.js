angular.module('app').controller('AdminController', ['$timeout', '$window', 'globals', 'api', 'notify', 'Video', 'admin',
    function($timeout, $window, globals, api, notify, Video, admin) {
        globals.title = 'Admin';

        var vm = angular.extend(this, {
            //properties
            serverVersionNumber: undefined,
            //api
            fetchMissingMetadata: fetchMissingMetadata,
            generateLibrary: generateLibrary,
            updateApplication: updateApplication
        });


        getVideoCounts();
        getServerVersionNumber();

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

        function getServerVersionNumber() {
            admin.getServerVersionNumber().then(function(version) {
                vm.serverVersionNumber = version;
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

        function updateApplication() {
            notify('Checking for updates. Please wait until this operation has completed', 'info');
            admin.updateApplication().then(function(result) {
                if (result.updateWasApplied) {
                    notify('Application has been updated. Reloading page.', 'success');
                    $timeout(function(){
                        $window.location.reload();
                    }, 4000);
                } else {
                    notify('No updates were found', 'success');
                }
            }, function() {
                notify('Unable to check and install updates', 'error');
            });
        }
    }]);