$(document).ready(function() {
    window.app = (window.app === undefined) ? {} : app;

    /**
     * determines how many pixels are avaible from below the navbar to the bottom of the nonscrollable portion of the screen.
     */
    app.displayHeight = function() {
        return $("body").height() - $("#bodyPadding").height();
    };

    /**
     * Shows/hides the waiting gif and an optional message
     * @param {boolean} bShow - if false, hide message. if true, show message.
     * @param {string} message - an optional message to display. If undefined, uses "Please wait."
     */
    app.wait = function(bShow, message) {
        bShow = (bShow === false) ? false : true;
        message = (message === undefined) ? "Please wait." : message;
        $("#waitMessage").html(message);
        if (bShow) {
            $("#waitModal").modal('show');
        } else {
            $("#waitModal").modal('hide');
        }
    };
    /**
     * Shows/hides the message screen
     * @param {boolean} bShow - if false, hide message. if true, show message.
     * @param {string} message - an optional message to display. If undefined, uses "Please wait."
     */
    app.message = function(bShow, message) {
        bShow = (bShow === false) ? false : true;
        message = (message === undefined) ? "Message" : message;
        $("#message").html(message);
        if (bShow) {
            $("#messageModal").modal('show');
        } else {
            $("#messageModal").modal('hide');
        }
    };
});