angular.module('app').service('globals', [function() {
        return {
            pageTitle: 'PlumMediaCenter',
            hideNavbar: false,
            infiniteScrollPageSize: 25,
            //whenever the admin page launches a metadata fetch, keep track of its status here. 
            fetchMissingMetadataIsPending: false,
            generateLibraryIsPending: false,
            checkForUpdatesIsPending: false
        };
    }]);