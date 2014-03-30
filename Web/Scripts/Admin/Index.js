$(document).ready(function() {
    function generateLibrary() {
        app.wait(true, "Generating library. Please wait.");
        plumapi.generateLibrary(function(returnVal) {
            app.wait(false);
            if (returnVal === true) {
                app.message(true, "Library has been successfully generated");
            } else {
                app.message(true, "There was an error generating the library");
            }
        });
    }

    function fetchMissingMetadata() {
        app.wait(true, "Fetching missing metadata and posters.");
        plumapi.FetchMissingMetadata(function(result) {
            app.wait(false);
            if (result.success === true) {
                app.message(true, "Successfully fetched all missing metadata and posters");
            } else {
                app.message(true, "There was an error fetching missing metadata and posters. Please see the <a href='Log.php'>log</a> for more information");
            }
        });
    }

    /**
     * Wires all events to elements that have them
     */
    (function() {
        $("#generateLibraryBtn").click(generateLibrary);
        $("#fetchMissingMetadataBtn").click(fetchMissingMetadata);
    })();
});