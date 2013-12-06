(function($) {
    /**
     * Disables an input 
     */
    $.fn.disable = function() {
        $(this).attr('disabled', 'disabled');
    };

    $.fn.enable = function() {
        $(this).removeAttr('disabled');
    };

}(jQuery));