$(document).ready(function() {
    bindEvents();
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

    /**
     * Wires all events to elements that have them
     */
    function bindEvents() {
        $("#generateLibraryBtn").click(generateLibrary);
    }
});